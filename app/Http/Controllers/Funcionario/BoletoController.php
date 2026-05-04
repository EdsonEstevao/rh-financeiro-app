<?php
// app/Http/Controllers/Funcionario/BoletoController.php

namespace App\Http\Controllers\Funcionario;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Controller;
use App\Models\Boleto;
use Barryvdh\DomPDF\Facade\PDF;

class BoletoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|funcionario']);
        $this->middleware('permission:funcionario.boletos.view');
    }

    /**
     * Display a listing of the user's boletos.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = $user->boletos()
            ->with(['createdBy'])
            ->latest('due_date');

        // Aplicar filtros
        $this->applyFilters($query, $request);

        // Estatísticas
        $stats = $this->getPersonalStats($user);

        $boletos = $query->paginate(12)->withQueryString();

        return view('funcionario.boletos.index', compact('boletos', 'stats'));
    }

    /**
     * Display the specified boleto.
     */
    public function show(Boleto $boleto): View
    {
        // Verificar se o boleto pertence ao usuário logado
        $this->authorizeBoletoAccess($boleto);

        $boleto->load(['createdBy', 'childBoletos', 'parentBoleto']);

        // Calcular valores atualizados
        $currentAmount = $boleto->total_with_charges;
        $isOverdue = $boleto->isOverdue();
        $daysOverdue = $boleto->days_overdue;

        return view('funcionario.boletos.show', compact(
            'boleto',
            'currentAmount',
            'isOverdue',
            'daysOverdue'
        ));
    }

    /**
     * Download boleto as PDF.
     */
    public function downloadPDF(Boleto $boleto)
    {
        $this->authorizeBoletoAccess($boleto);

        try {
            $pdf = PDF::loadView('financeiro.boletos.pdf', [
                'boleto' => $boleto,
                'beneficiary' => [
                    'name' => config('app.name'),
                    'document' => config('app.beneficiary_document', '00.000.000/0000-00'),
                ],
                'show_copy' => true,
            ]);

            $pdf->setPaper('a4');

            $filename = sprintf(
                'boleto-%s-%s.pdf',
                $boleto->boleto_number,
                $boleto->due_date->format('d-m-Y')
            );

            // Log do download
            activity()
                ->performedOn($boleto)
                ->causedBy(auth()->user())
                ->log('Boleto baixado pelo funcionário');

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Failed to download boleto PDF for user', [
                'error' => $e->getMessage(),
                'boleto_id' => $boleto->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Erro ao gerar PDF do boleto. Por favor, tente novamente.');
        }
    }

    /**
     * Stream boleto as PDF in browser.
     */
    public function streamPDF(Boleto $boleto)
    {
        $this->authorizeBoletoAccess($boleto);

        $pdf = PDF::loadView('financeiro.boletos.pdf', [
            'boleto' => $boleto,
            'beneficiary' => [
                'name' => config('app.name'),
                'document' => config('app.beneficiary_document', '00.000.000/0000-00'),
            ],
        ]);

        return $pdf->stream("boleto-{$boleto->boleto_number}.pdf");
    }

    /**
     * Get boleto data as JSON for API consumption.
     */
    public function getBoletoData(Boleto $boleto): \Illuminate\Http\JsonResponse
    {
        $this->authorizeBoletoAccess($boleto);

        return response()->json([
            'id' => $boleto->id,
            'boleto_number' => $boleto->boleto_number,
            'amount' => number_format($boleto->amount, 2, ',', '.'),
            'due_date' => $boleto->due_date->format('d/m/Y'),
            'status' => $boleto->status,
            'status_label' => $this->getStatusLabel($boleto),
            'digitable_line' => $boleto->digitable_line,
            'barcode' => $boleto->barcode,
            'is_overdue' => $boleto->isOverdue(),
            'days_overdue' => $boleto->days_overdue,
            'total_with_charges' => number_format($boleto->total_with_charges, 2, ',', '.'),
            'description' => $boleto->description,
            'payer_name' => $boleto->payer_name,
            'instructions' => $boleto->instructions,
        ]);
    }

    /**
     * Get boletos summary for dashboard.
     */
    public function summary(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();
        $stats = $this->getPersonalStats($user);

        return response()->json($stats);
    }

    /**
     * Apply filters to the boleto query.
     */
    private function applyFilters($query, Request $request): void
    {
        $query->when($request->filled('status'), function ($q) use ($request) {
            if ($request->status === 'overdue') {
                $q->where(function ($sq) {
                    $sq->where('status', 'overdue')
                       ->orWhere(function ($ssq) {
                           $ssq->where('status', 'pending')
                               ->where('due_date', '<', now()->startOfDay());
                       });
                });
            } elseif ($request->status === 'pending_not_overdue') {
                $q->where('status', 'pending')
                  ->where('due_date', '>=', now()->startOfDay());
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

        $query->when($request->filled('month'), function ($q) use ($request) {
            $q->whereMonth('due_date', $request->month);
        });

        $query->when($request->filled('year'), function ($q) use ($request) {
            $q->whereYear('due_date', $request->year);
        });

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('boleto_number', 'like', "%{$search}%")
                   ->orWhere('description', 'like', "%{$search}%")
                   ->orWhere('our_number', 'like', "%{$search}%");
            });
        });

        // Ordenação
        $sortField = $request->get('sort', 'due_date');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = ['due_date', 'amount', 'status', 'created_at', 'boleto_number'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection === 'asc' ? 'asc' : 'desc');
        }
    }

    /**
     * Get personalized statistics for the user.
     */
    private function getPersonalStats($user): array
    {
        $boletos = $user->boletos();

        $pendingBoletos = (clone $boletos)->where('status', 'pending')
            ->where('due_date', '>=', now()->startOfDay());

        $overdueBoletos = (clone $boletos)->where(function ($q) {
            $q->where('status', 'overdue')
              ->orWhere(function ($sq) {
                  $sq->where('status', 'pending')
                     ->where('due_date', '<', now()->startOfDay());
              });
        });

        $paidBoletos = (clone $boletos)->where('status', 'paid');

        $thisMonth = (clone $boletos)->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        $nextDue = (clone $boletos)->where('status', 'pending')
            ->where('due_date', '>=', now()->startOfDay())
            ->orderBy('due_date')
            ->first();

        return [
            'total_count' => $boletos->count(),
            'total_amount' => $boletos->sum('amount'),

            'pending_count' => $pendingBoletos->count(),
            'pending_amount' => $pendingBoletos->sum('amount'),

            'overdue_count' => $overdueBoletos->count(),
            'overdue_amount' => $overdueBoletos->sum('amount'),

            'paid_count' => $paidBoletos->count(),
            'paid_amount' => $paidBoletos->sum('amount'),
            'paid_this_year' => $paidBoletos->whereYear('paid_at', now()->year)->sum('amount'),

            'this_month_count' => $thisMonth->count(),
            'this_month_amount' => $thisMonth->sum('amount'),

            'next_due_date' => $nextDue?->due_date,
            'next_due_amount' => $nextDue?->amount,
            'next_due_description' => $nextDue?->description,

            'average_amount' => $boletos->count() > 0 ? $boletos->avg('amount') : 0,
            'largest_amount' => $boletos->max('amount') ?? 0,

            'paid_percentage' => $boletos->count() > 0
                ? round(($paidBoletos->count() / $boletos->count()) * 100)
                : 0,
        ];
    }

    /**
     * Authorize boleto access.
     */
    private function authorizeBoletoAccess(Boleto $boleto): void
    {
        if ($boleto->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Você não tem permissão para acessar este boleto.');
        }
    }

    /**
     * Get status label for display.
     */
    private function getStatusLabel(Boleto $boleto): string
    {
        if ($boleto->status === 'paid') {
            return 'Pago';
        }

        if ($boleto->status === 'pending' && $boleto->isOverdue()) {
            return 'Vencido';
        }

        return match ($boleto->status) {
            'pending' => 'Pendente',
            'cancelled' => 'Cancelado',
            'protested' => 'Protestado',
            'returned' => 'Devolvido',
            default => ucfirst($boleto->status),
        };
    }
}