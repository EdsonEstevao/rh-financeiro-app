<?php
// app/Http/Controllers/Financeiro/ContasReceberController.php

namespace App\Http\Controllers\Financeiro;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\{ContaReceber, User};
use Barryvdh\DomPDF\Facade\Pdf;

class ContasReceberController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin,financeiro,consultor']);
    }

    /**
     * Dashboard de contas a receber.
     */
    public function index(Request $request): View
    {
        $query = ContaReceber::with([
            'cliente', 'boleto', 'consultant', 'createdBy', 'receivedBy'
        ])->latest('data_vencimento');

        // Aplicar filtros
        $this->applyFilters($query, $request);

        $contas = $query->paginate(15)->withQueryString();

        // Estatísticas
        $stats = $this->getStats();
        $aging = ContaReceber::getAging();

        // Listas para filtros
        $clientes = User::role('funcionario')->orderBy('name')->get();
        $consultores = User::role('consultor')->orderBy('name')->get();
        $tipos = $this->getTiposReceita();

        return view('financeiro.contas-receber.index', compact(
            'contas', 'stats', 'aging', 'clientes', 'consultores', 'tipos'
        ));
    }

    /**
     * Formulário de nova conta a receber.
     */
    public function create(): View
    {
        $clientes = User::where('is_active', true)->orderBy('name')->get();
        $consultores = User::role('consultor')->orderBy('name')->get();
        $tipos = $this->getTiposReceita();
        $formasPagamento = $this->getFormasPagamento();

        return view('financeiro.contas-receber.create', compact(
            'clientes', 'consultores', 'tipos', 'formasPagamento'
        ));
    }

    /**
     * Salvar nova conta a receber.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cliente_id' => ['nullable', 'exists:users,id'],
            'tipo' => ['required', 'string'],
            'forma_pagamento' => ['nullable', 'string'],
            'cliente_nome' => ['required', 'string', 'max:200'],
            'cliente_documento' => ['nullable', 'string', 'max:20'],
            'cliente_email' => ['nullable', 'email', 'max:100'],
            'valor_original' => ['required', 'numeric', 'min:0.01'],
            'data_emissao' => ['required', 'date'],
            'data_vencimento' => ['required', 'date', 'after_or_equal:data_emissao'],
            'descricao' => ['required', 'string', 'max:200'],
            'categoria' => ['nullable', 'string', 'max:50'],
            'consultant_id' => ['nullable', 'exists:users,id'],
            'has_commission' => ['boolean'],
            'commission_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'observacoes' => ['nullable', 'string'],
            'total_parcelas' => ['nullable', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            $conta = ContaReceber::create([
                ...$validated,
                'created_by' => auth()->id(),
                'status' => 'pendente',
                'valor_total' => $validated['valor_original'],
            ]);

            // Se parcelado, gerar parcelas
            if (!empty($validated['total_parcelas']) && $validated['total_parcelas'] > 1) {
                $valorParcela = $validated['valor_original'] / $validated['total_parcelas'];

                for ($i = 2; $i <= $validated['total_parcelas']; $i++) {
                    ContaReceber::create([
                        ...$validated,
                        'fatura_id' => $conta->id,
                        'parcela_atual' => $i,
                        'total_parcelas' => $validated['total_parcelas'],
                        'valor_original' => $valorParcela,
                        'valor_total' => $valorParcela,
                        'data_vencimento' => Carbon::parse($validated['data_vencimento'])->addMonths($i - 1),
                        'numero_documento' => null, // Será gerado automaticamente
                    ]);
                }

                // Atualizar a primeira parcela
                $conta->update([
                    'parcela_atual' => 1,
                    'total_parcelas' => $validated['total_parcelas'],
                    'valor_original' => $valorParcela,
                    'valor_total' => $valorParcela,
                ]);
            }

            DB::commit();

            activity()
                ->performedOn($conta)
                ->causedBy(auth()->user())
                ->log('Conta a receber criada');

            return redirect()
                ->route('financeiro.contas-receber.show', $conta)
                ->with('success', 'Conta a receber registrada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar conta a receber', ['error' => $e->getMessage()]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao registrar conta: ' . $e->getMessage());
        }
    }

    /**
     * Visualizar conta.
     */
    public function show(ContaReceber $conta): View
    {
        $conta->load([
            'cliente', 'boleto', 'creditCardTransaction',
            'createdBy', 'receivedBy', 'consultant',
            'fatura', 'parcelas'
        ]);

        return view('financeiro.contas-receber.show', compact('conta'));
    }

    /**
     * Marcar como recebido.
     */
    public function markAsReceived(Request $request, ContaReceber $conta): RedirectResponse
    {
        $validated = $request->validate([
            'valor_recebido' => ['required', 'numeric', 'min:0.01'],
            'data_recebimento' => ['required', 'date'],
            'comprovante' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        try {
            DB::beginTransaction();

            $conta->markAsReceived(
                valorRecebido: $validated['valor_recebido'],
                dataRecebimento: Carbon::parse($validated['data_recebimento']),
                receivedBy: auth()->id()
            );

            // Upload comprovante
            if ($request->hasFile('comprovante')) {
                $path = $request->file('comprovante')
                    ->store('comprovantes-recebimentos', 'public');
                $conta->update(['comprovante_path' => $path]);
            }

            DB::commit();

            activity()
                ->performedOn($conta)
                ->causedBy(auth()->user())
                ->log('Conta marcada como recebida');

            return back()->with('success', 'Recebimento confirmado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao processar recebimento.');
        }
    }

    /**
     * Enviar cobrança.
     */
    public function enviarCobranca(ContaReceber $conta): RedirectResponse
    {
        $conta->enviarCobranca();

        return back()->with('success', 'Cobrança enviada com sucesso!');
    }

    /**
     * Cancelar conta.
     */
    public function cancel(Request $request, ContaReceber $conta): RedirectResponse
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
     * Estatísticas do dashboard.
     */
    private function getStats(): array
    {
        $hoje = now();
        $mesAtual = $hoje->copy()->startOfMonth();

        return [
            'total_abertas' => ContaReceber::abertas()->count(),
            'valor_abertas' => ContaReceber::abertas()->sum('valor_total'),

            'total_vencidas' => ContaReceber::vencidas()->count(),
            'valor_vencidas' => ContaReceber::vencidas()->sum('valor_total'),

            'total_a_vencer' => ContaReceber::aVencer()->count(),
            'valor_a_vencer' => ContaReceber::aVencer()->sum('valor_total'),

            'total_recebido_hoje' => ContaReceber::where('status', 'recebido')
                ->whereDate('data_recebimento', $hoje)->count(),
            'valor_recebido_hoje' => ContaReceber::where('status', 'recebido')
                ->whereDate('data_recebimento', $hoje)->sum('valor_recebido'),

            'total_recebido_mes' => ContaReceber::where('status', 'recebido')
                ->where('data_recebimento', '>=', $mesAtual)->count(),
            'valor_recebido_mes' => ContaReceber::where('status', 'recebido')
                ->where('data_recebimento', '>=', $mesAtual)->sum('valor_recebido'),

            'comissoes_pendentes' => ContaReceber::comissoesPendentes()->count(),
            'valor_comissoes_pendentes' => ContaReceber::comissoesPendentes()
                ->sum('commission_amount'),

            'vencem_7_dias' => ContaReceber::vencemEm(7)->count(),
            'valor_vencem_7_dias' => ContaReceber::vencemEm(7)->sum('valor_total'),
        ];
    }

    /**
     * Aplicar filtros.
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            match ($request->status) {
                'vencidas' => $query->vencidas(),
                'a_vencer' => $query->aVencer(),
                'recebidas' => $query->recebidas(),
                'abertas' => $query->abertas(),
                default => $query->where('status', $request->status),
            };
        }

        $query->when($request->filled('tipo'), fn($q) => $q->where('tipo', $request->tipo));
        $query->when($request->filled('cliente_id'), fn($q) => $q->where('cliente_id', $request->cliente_id));
        $query->when($request->filled('consultant_id'), fn($q) => $q->where('consultant_id', $request->consultant_id));
        $query->when($request->filled('forma_pagamento'), fn($q) => $q->where('forma_pagamento', $request->forma_pagamento));
        $query->when($request->filled('priority'), fn($q) => $q->where('priority', $request->priority));

        $query->when($request->filled('date_from'),
            fn($q) => $q->whereDate('data_vencimento', '>=', $request->date_from));
        $query->when($request->filled('date_to'),
            fn($q) => $q->whereDate('data_vencimento', '<=', $request->date_to));

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('numero_documento', 'like', "%{$search}%")
                   ->orWhere('cliente_nome', 'like', "%{$search}%")
                   ->orWhere('cliente_documento', 'like', "%{$search}%")
                   ->orWhere('descricao', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Tipos de receita.
     */
    private function getTiposReceita(): array
    {
        return [
            'boleto' => 'Boleto',
            'cartao_credito' => 'Cartão de Crédito',
            'cartao_debito' => 'Cartão de Débito',
            'pix' => 'PIX',
            'transferencia' => 'Transferência',
            'dinheiro' => 'Dinheiro',
            'cheque' => 'Cheque',
            'nota_fiscal' => 'Nota Fiscal',
            'fatura' => 'Fatura',
            'mensalidade' => 'Mensalidade',
            'servico' => 'Serviço',
            'produto' => 'Produto',
            'comissao' => 'Comissão',
            'aluguel' => 'Aluguel',
            'outros' => 'Outros',
        ];
    }

    /**
     * Formas de pagamento.
     */
    private function getFormasPagamento(): array
    {
        return [
            'boleto' => 'Boleto',
            'cartao_credito' => 'Cartão de Crédito',
            'cartao_debito' => 'Cartão de Débito',
            'pix' => 'PIX',
            'transferencia' => 'Transferência',
            'dinheiro' => 'Dinheiro',
            'cheque' => 'Cheque',
            'debito_automatico' => 'Débito Automático',
            'carteira_digital' => 'Carteira Digital',
            'outros' => 'Outros',
        ];
    }
}
