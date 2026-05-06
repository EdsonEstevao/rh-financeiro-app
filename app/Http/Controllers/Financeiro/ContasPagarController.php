<?php
// app/Http/Controllers/Financeiro/ContasPagarController.php

namespace App\Http\Controllers\Financeiro;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{Auth, DB, Log};
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\{ContaPagar, Department, Fornecedor};
use Barryvdh\DomPDF\Facade\Pdf;


class ContasPagarController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,financeiro']);
    }

    /**
     * Dashboard de contas a pagar.
     */
    public function index(Request $request): View
    {
        $query = ContaPagar::with(['fornecedor', 'createdBy', 'department'])
            ->latest('data_vencimento');

        // Aplicar filtros
        $this->applyFilters($query, $request);

        $contas = $query->paginate(15)->withQueryString();

        // Estatísticas
        $stats = $this->getStats();

        // Listas para filtros
        $fornecedores = Fornecedor::orderBy('nome', 'asc')->get();
        $departments = Department::orderBy('name', 'asc')->get();

        return view('financeiro.contas-pagar.index-new', compact(
            'contas', 'stats', 'fornecedores', 'departments'
        ));
    }

    /**
     * Formulário para criar nova conta.
     */
    public function create(): View
    {
        $fornecedores = Fornecedor::orderBy('nome', 'asc')->get();
        $departments = Department::orderBy('name', 'asc')->get();
        $tipos = $this->getTiposConta();

        return view('financeiro.contas-pagar.create', compact(
            'fornecedores', 'departments', 'tipos'
        ));
    }

    /**
     * Salvar nova conta a pagar.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fornecedor_id' => ['nullable', 'exists:fornecedores,id'],
            'tipo' => ['required', 'string'],
            'beneficiario_nome' => ['required', 'string', 'max:200'],
            'beneficiario_documento' => ['nullable', 'string', 'max:20'],
            'valor_original' => ['required', 'numeric', 'min:0.01'],
            'data_emissao' => ['required', 'date'],
            'data_vencimento' => ['required', 'date', 'after_or_equal:data_emissao'],
            'descricao' => ['required', 'string', 'max:200'],
            'categoria' => ['nullable', 'string', 'max:50'],
            'centro_custo' => ['nullable', 'string', 'max:50'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'observacoes' => ['nullable', 'string'],
            'codigo_barras' => ['nullable', 'string'],
            'linha_digitavel' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $conta = ContaPagar::create([
                ...$validated,
                'numero_documento' => $this->generateDocumentNumber(),
                'created_by' => Auth::id(),
                'status' => 'pendente',
                'valor_total' => $validated['valor_original'],
                'priority' => 'media',
            ]);

            DB::commit();

            activity()
                ->performedOn($conta)
                ->causedBy(Auth::user())
                ->withProperties(['valor' => $conta->valor_total, 'vencimento' => $conta->data_vencimento->format('d/m/Y')])
                ->log('Conta a pagar criada');

            return redirect()
                ->route('financeiro.contas-pagar.show', $conta)
                ->with('success', 'Conta a pagar registrada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar conta a pagar', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao registrar conta: ' . $e->getMessage());
        }
    }

    /**
     * Visualizar conta.
     */
    public function show(ContaPagar $conta): View
    {
        $conta->load(['fornecedor', 'createdBy', 'approvedBy', 'paidBy', 'department', 'parcelas']);

        return view('financeiro.contas-pagar.show', compact('conta'));
    }

    /**
     * Marcar conta como paga.
     */
    public function markAsPaid(Request $request, ContaPagar $conta): RedirectResponse
    {
        $validated = $request->validate([
            'valor_pago' => ['required', 'numeric', 'min:0.01'],
            'data_pagamento' => ['required', 'date'],
            'comprovante' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        try {
            DB::beginTransaction();

            $conta->update([
                'status' => 'pago',
                'valor_pago' => $validated['valor_pago'],
                'data_pagamento' => $validated['data_pagamento'],
                'paid_by' => Auth::id(),
            ]);

            if ($request->hasFile('comprovante')) {
                $path = $request->file('comprovante')->store('comprovantes', 'public');
                $conta->update(['comprovante_path' => $path]);
            }

            DB::commit();

            activity()
                ->performedOn($conta)
                ->causedBy(Auth::user())
                ->log('Conta marcada como paga');

            return back()->with('success', 'Conta marcada como paga com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao marcar conta como paga', ['error' => $e->getMessage()]);

            return back()->with('error', 'Erro ao processar pagamento.');
        }
    }

    /**
     * Aprovar conta para pagamento.
     */
    public function approve(ContaPagar $conta): RedirectResponse
    {
        if ($conta->status !== 'pendente') {
            return back()->with('error', 'Apenas contas pendentes podem ser aprovadas.');
        }

        $conta->update([
            'status' => 'aprovado',
            'approved_by' => Auth::id(),
            'data_aprovacao' => now(),
        ]);

        return back()->with('success', 'Conta aprovada para pagamento!');
    }

    /**
     * Cancelar conta.
     */
    public function cancel(Request $request, ContaPagar $conta): RedirectResponse
    {
        $validated = $request->validate([
            'motivo' => ['required', 'string', 'max:255'],
        ]);

        $conta->update([
            'status' => 'cancelado',
            'status_motivo' => $validated['motivo'],
        ]);

        return back()->with('success', 'Conta cancelada.');
    }

    /**
     * Gerar número de documento.
     */
    private function generateDocumentNumber(): string
    {
        $prefix = 'CP';
        $year = date('Y');
        $month = date('m');
        $sequential = str_pad(ContaPagar::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}{$month}-{$sequential}";
    }

    /**
     * Aplicar filtros.
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            if ($request->status === 'vencidas') {
                $query->vencidas();
            } elseif ($request->status === 'a_vencer') {
                $query->aVencer();
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->fornecedor_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('data_vencimento', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('data_vencimento', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('numero_documento', 'like', "%{$search}%")
                  ->orWhere('beneficiario_nome', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Estatísticas.
     */
    private function getStats(): array
    {
        $totalPendente = ContaPagar::pendentes()->sum('valor_total');
        $totalVencido = ContaPagar::vencidas()->sum('valor_total');
        $totalPagoMes = ContaPagar::query()->where('status', 'pago')
            ->whereMonth('data_pagamento', now()->month)
            ->sum('valor_pago');
        $totalAVencer = ContaPagar::aVencer()
            ->where('data_vencimento', '>=', now())
            ->where('data_vencimento', '<=', now()->addDays(30))
            ->sum('valor_total');

        return [
            'total_pendente' => ContaPagar::pendentes()->count('id'),
            'valor_pendente' => $totalPendente,
            'total_vencido' => ContaPagar::vencidas()->count('id'),
            'valor_vencido' => $totalVencido,
            'total_pago_mes' => ContaPagar::query()->where('status', 'pago')
                ->whereMonth('data_pagamento', now()->month)->count('id'),
            'valor_pago_mes' => $totalPagoMes,
            'a_vencer_30_dias' => ContaPagar::aVencer()
                ->where('data_vencimento', '<=', now()->addDays(30))->count('id'),
            'valor_a_vencer_30_dias' => $totalAVencer,
        ];
    }

    /**
     * Tipos de conta.
     */
    private function getTiposConta(): array
    {
        return [
            'boleto' => 'Boleto',
            'cartao_credito' => 'Cartão de Crédito',
            'transferencia' => 'Transferência',
            'pix' => 'PIX',
            'boleto_fatura' => 'Boleto/Fatura',
            'imposto' => 'Imposto',
            'fornecedor' => 'Fornecedor',
            'servico' => 'Serviço',
            'aluguel' => 'Aluguel',
            'condominio' => 'Condomínio',
            'energia' => 'Energia',
            'agua' => 'Água',
            'telefone' => 'Telefone',
            'internet' => 'Internet',
            'seguro' => 'Seguro',
            'outros' => 'Outros',
        ];
    }
}
