<?php
// app/Http/Controllers/Financeiro/BoletoController.php

namespace App\Http\Controllers\Financeiro;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\{Boleto, User};
use App\Services\{BillingService, ReportService};
use Barryvdh\DomPDF\Facade\Pdf;

class BoletoController extends Controller
{
    public function __construct(
        private readonly BillingService $billingService,
        private readonly ReportService $reportService
    ) {
        $this->middleware(['auth', 'role:admin|financeiro|consultor']);

        // Permissões granulares
        $this->middleware('permission:financeiro.boletos.create')->only(['create', 'store']);
        $this->middleware('permission:financeiro.boletos.edit')->only(['edit', 'update']);
        $this->middleware('permission:financeiro.boletos.delete')->only(['destroy']);
        $this->middleware('permission:financeiro.boletos.cancel')->only(['cancel']);
        $this->middleware('permission:financeiro.boletos.mark-paid')->only(['markAsPaid']);
    }

    /**
     * Display a listing of boletos.
     */
    public function index(Request $request): View
    {
        $query = Boleto::with(['user', 'createdBy'])
            ->withSum('childBoletos', 'amount')
            ->latest('due_date');

        // Aplicar filtros
        $this->applyFilters($query, $request);

        // Estatísticas para os cards
        $stats = $this->getStats();

        $boletos = $query->paginate(15)->withQueryString();

        // Lista de clientes para o filtro
        $clients = User::whereHas('boletos')
            ->orWhereHas('roles', fn($q) => $q->whereIn('name', ['funcionario']))
            ->orderBy('name')
            ->get(['id', 'name', 'cpf']);

        return view('financeiro.boletos.index', compact('boletos', 'stats', 'clients'));
    }

    /**
     * Show the form for creating a new boleto.
     */
    public function create(): View
    {
        $clients = User::whereHas('roles', fn($q) =>
            $q->whereIn('name', ['funcionario', 'consultor', 'gerente'])
        )
        ->where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name', 'cpf', 'email', 'phone']);

        return view('financeiro.boletos.create', compact('clients'));
    }

    /**
     * Store a newly created boleto.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'payer_name' => ['required', 'string', 'max:200'],
            'payer_document' => ['required', 'string', 'max:20'],
            'payer_email' => ['nullable', 'email', 'max:100'],
            'payer_phone' => ['nullable', 'string', 'max:20'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'description' => ['required', 'string', 'max:500'],
            'fine_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'interest_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'instructions' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::beginTransaction();

            // Preparar dados do boleto
            $boletoData = array_merge($validated, [
                'boleto_number' => Boleto::generateBoletoNumber(),
                'our_number' => $this->billingService->generateOurNumber(),
                'created_by' => auth()->id(),
                'status' => 'pending',
                'total_amount' => $this->billingService->calculateTotalAmount(
                    $validated['amount'],
                    $validated['discount_amount'] ?? 0,
                    $validated['fine_percentage'] ?? 0,
                    $validated['interest_percentage'] ?? 0
                ),
            ]);

            // Gerar código de barras e linha digitável (mock)
            $boletoData['barcode'] = $this->billingService->generateBarcode();
            $boletoData['digitable_line'] = $this->billingService->generateDigitableLine($boletoData['barcode']);

            $boleto = Boleto::create($boletoData);

            DB::commit();

            // Log da atividade
            activity()
                ->performedOn($boleto)
                ->causedBy(auth()->user())
                ->withProperties([
                    'amount' => $boleto->amount,
                    'due_date' => $boleto->due_date->format('d/m/Y'),
                ])
                ->log('Boleto criado');

            Log::info('Boleto created', [
                'boleto_id' => $boleto->id,
                'user_id' => auth()->id(),
                'amount' => $boleto->amount
            ]);

            return redirect()
                ->route('financeiro.boletos.show', $boleto)
                ->with('success', 'Boleto #' . $boleto->boleto_number . ' gerado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create boleto', [
                'error' => $e->getMessage(),
                'data' => $request->except(['_token']),
                'user_id' => auth()->id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao gerar boleto. Por favor, tente novamente.');
        }
    }

    /**
     * Display the specified boleto.
     */
    public function show(Boleto $boleto): View
    {
        $boleto->load(['user', 'createdBy', 'childBoletos', 'parentBoleto']);

        // Calcular valores atualizados se vencido
        $currentAmount = $boleto->total_with_charges;

        return view('financeiro.boletos.show', compact('boleto', 'currentAmount'));
    }

    /**
     * Show the form for editing the specified boleto.
     */
    public function edit(Boleto $boleto): View
    {
        // Verificar se o boleto pode ser editado
        if (in_array($boleto->status, ['paid', 'cancelled'])) {
            return redirect()
                ->route('financeiro.boletos.show', $boleto)
                ->with('warning', 'Este boleto não pode ser editado pois está ' .
                    ($boleto->status === 'paid' ? 'pago' : 'cancelado') . '.');
        }

        return view('financeiro.boletos.edit', compact('boleto'));
    }

    /**
     * Update the specified boleto.
     */
    public function update(Request $request, Boleto $boleto): RedirectResponse
    {
        if (in_array($boleto->status, ['paid', 'cancelled'])) {
            return back()->with('error', 'Boletos pagos ou cancelados não podem ser alterados.');
        }

        $validated = $request->validate([
            'payer_name' => ['required', 'string', 'max:200'],
            'payer_document' => ['required', 'string', 'max:20'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:500'],
            'status' => ['nullable', 'in:pending,paid,cancelled'],
            'instructions' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            DB::beginTransaction();

            $originalData = $boleto->getOriginal();

            $boleto->update(array_merge($validated, [
                'total_amount' => $validated['amount'],
                'updated_by' => auth()->id(),
            ]));

            DB::commit();

            // Log da atividade
            activity()
                ->performedOn($boleto)
                ->causedBy(auth()->user())
                ->withProperties([
                    'changes' => [
                        'before' => array_intersect_key($originalData, $validated),
                        'after' => $validated,
                    ]
                ])
                ->log('Boleto atualizado');

            return redirect()
                ->route('financeiro.boletos.show', $boleto)
                ->with('success', 'Boleto atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update boleto', [
                'error' => $e->getMessage(),
                'boleto_id' => $boleto->id,
                'user_id' => auth()->id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar boleto.');
        }
    }

    /**
     * Remove the specified boleto.
     */
    public function destroy(Boleto $boleto): RedirectResponse
    {
        try {
            $boletoNumber = $boleto->boleto_number;

            DB::beginTransaction();

            // Cancelar boletos filhos recursivos
            $boleto->childBoletos()->update([
                'status' => 'cancelled',
                'status_reason' => 'Boleto pai cancelado'
            ]);

            $boleto->delete();

            DB::commit();

            activity()
                ->performedOn($boleto)
                ->causedBy(auth()->user())
                ->log('Boleto excluído');

            return redirect()
                ->route('financeiro.boletos.index')
                ->with('success', "Boleto #{$boletoNumber} excluído com sucesso!");

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete boleto', [
                'error' => $e->getMessage(),
                'boleto_id' => $boleto->id
            ]);

            return back()->with('error', 'Erro ao excluir boleto.');
        }
    }

    /**
     * Mark boleto as paid.
     */
    public function markAsPaid(Boleto $boleto): RedirectResponse
    {
        if ($boleto->status !== 'pending') {
            return back()->with('error', 'Apenas boletos pendentes podem ser marcados como pagos.');
        }

        try {
            $boleto->markAsPaid(
                amountPaid: $boleto->total_with_charges,
                paidAt: now()
            );

            activity()
                ->performedOn($boleto)
                ->causedBy(auth()->user())
                ->withProperties(['paid_amount' => $boleto->total_with_charges])
                ->log('Boleto marcado como pago');

            return back()->with('success', 'Boleto marcado como pago com sucesso!');

        } catch (\Exception $e) {
            Log::error('Failed to mark boleto as paid', [
                'error' => $e->getMessage(),
                'boleto_id' => $boleto->id
            ]);

            return back()->with('error', 'Erro ao marcar boleto como pago.');
        }
    }

    /**
     * Cancel boleto.
     */
    public function cancel(Boleto $boleto, Request $request): RedirectResponse
    {
        if (in_array($boleto->status, ['paid', 'cancelled'])) {
            return back()->with('error', 'Este boleto não pode ser cancelado.');
        }

        try {
            $reason = $request->input('reason', 'Cancelado pelo operador');

            $boleto->cancel($reason);

            // Cancelar boletos filhos
            $boleto->childBoletos()
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                    'status_reason' => 'Boleto pai cancelado'
                ]);

            activity()
                ->performedOn($boleto)
                ->causedBy(auth()->user())
                ->withProperties(['reason' => $reason])
                ->log('Boleto cancelado');

            return back()->with('success', 'Boleto cancelado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Failed to cancel boleto', [
                'error' => $e->getMessage(),
                'boleto_id' => $boleto->id
            ]);

            return back()->with('error', 'Erro ao cancelar boleto.');
        }
    }

    /**
     * Download boleto PDF.
     */
    public function downloadPDF(Boleto $boleto)
    {
        try {
            $pdf = PDF::loadView('financeiro.boletos.pdf', [
                'boleto' => $boleto,
                'beneficiary' => [
                    'name' => config('app.name'),
                    'document' => config('app.beneficiary_document', '00.000.000/0000-00'),
                ]
            ]);

            $pdf->setPaper('a4');

            $filename = "boleto-{$boleto->boleto_number}.pdf";

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Failed to generate boleto PDF', [
                'error' => $e->getMessage(),
                'boleto_id' => $boleto->id
            ]);

            return back()->with('error', 'Erro ao gerar PDF do boleto.');
        }
    }

    /**
     * Stream boleto PDF.
     */
    public function streamPDF(Boleto $boleto)
    {
        $pdf = PDF::loadView('financeiro.boletos.pdf', [
            'boleto' => $boleto,
            'beneficiary' => [
                'name' => config('app.name'),
                'document' => config('app.beneficiary_document'),
            ]
        ]);

        return $pdf->stream("boleto-{$boleto->boleto_number}.pdf");
    }

    /**
     * Send boleto by email.
     */
    public function sendByEmail(Request $request, Boleto $boleto): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            // Implementar envio de email
            // Mail::to($request->email)->send(new BoletoMail($boleto));

            $boleto->update([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);

            return back()->with('success', 'Boleto enviado por email com sucesso!');

        } catch (\Exception $e) {
            Log::error('Failed to send boleto by email', [
                'error' => $e->getMessage(),
                'boleto_id' => $boleto->id
            ]);

            return back()->with('error', 'Erro ao enviar boleto por email.');
        }
    }

    /**
     * Apply filters to query.
     */
    private function applyFilters($query, Request $request): void
    {
        $query->when($request->filled('status'), function ($q) use ($request) {
            if ($request->status === 'overdue') {
                $q->where(function ($sq) {
                    $sq->where('status', 'overdue')
                       ->orWhere(function ($ssq) {
                           $ssq->where('status', 'pending')
                               ->where('due_date', '<', now());
                       });
                });
            } else {
                $q->where('status', $request->status);
            }
        });

        $query->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('due_date', '>=', $request->date_from);
        });

        $query->when($request->filled('date_to'), function ($q) use ($request) {
            $q->whereDate('due_date', '<=', $request->date_to);
        });

        $query->when($request->filled('user_id'), function ($q) use ($request) {
            $q->where('user_id', $request->user_id);
        });

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('boleto_number', 'like', "%{$search}%")
                   ->orWhere('payer_name', 'like', "%{$search}%")
                   ->orWhere('description', 'like', "%{$search}%")
                   ->orWhere('our_number', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Get statistics for dashboard cards.
     */
    private function getStats(): array
    {
        return [
            'total' => Boleto::count(),
            'total_amount' => Boleto::sum('amount'),
            'pending' => Boleto::where('status', 'pending')->count(),
            'paid_today' => Boleto::where('status', 'paid')
                ->whereDate('paid_at', today())
                ->sum('amount'),
            'overdue' => Boleto::where(function ($q) {
                $q->where('status', 'overdue')
                  ->orWhere(function ($sq) {
                      $sq->where('status', 'pending')
                         ->where('due_date', '<', now());
                  });
            })->count(),
        ];
    }
}
