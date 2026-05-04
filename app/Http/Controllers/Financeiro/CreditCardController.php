<?php
// app/Http/Controllers/Financeiro/CreditCardController.php

namespace App\Http\Controllers\Financeiro;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{Auth, DB, Log};
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\{CreditCardTransaction, User};
use App\Services\PaymentGateway;
use Barryvdh\DomPDF\Facade\PDF;

class CreditCardController extends Controller
{
    public function __construct(
        private readonly PaymentGateway $paymentGateway
    ) {
        $this->middleware(['auth', 'role:admin,financeiro,consultor']);
    }

    /**
     * Display a listing of credit card transactions.
     */
    public function index(Request $request): View
    {
        $query = CreditCardTransaction::with('user')->latest();

        // Aplicar filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('card_brand')) {
            $query->where('card_brand', $request->card_brand);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                  ->orWhere('authorization_code', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $transactions = $query->paginate(15)->withQueryString();

        // Estatísticas
        $stats = $this->getStats();

        return view('financeiro.credit-cards.index', compact('transactions', 'stats'));
    }

    /**
     * Show form to create a new transaction.
     */
    public function create(): View
    {
        $users = User::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'cpf', 'email']);

        return view('financeiro.credit-cards.create', compact('users'));
    }

    /**
     * Store a new transaction (from form).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'installments' => ['required', 'integer', 'min:1', 'max:12'],
            'card_number' => ['required', 'string', 'min:13', 'max:19'],
            'card_holder_name' => ['required', 'string', 'max:200'],
            'card_expiration_month' => ['required', 'string', 'size:2'],
            'card_expiration_year' => ['required', 'string', 'size:4'],
            'card_cvv' => ['required', 'string', 'size:3'],
            'description' => ['required', 'string', 'max:500'],
        ]);

        try {
            DB::beginTransaction();

            $user = User::findOrFail($validated['user_id']);

            // Preparar dados para o gateway
            $paymentData = [
                'amount' => $validated['amount'],
                'installments' => $validated['installments'],
                'description' => $validated['description'],
                'card' => [
                    'number' => $validated['card_number'],
                    'holder_name' => $validated['card_holder_name'],
                    'expiration_month' => $validated['card_expiration_month'],
                    'expiration_year' => $validated['card_expiration_year'],
                    'cvv' => $validated['card_cvv'],
                ],
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_document' => $user->cpf,
                'user_id' => $user->id,
            ];

            // Processar pagamento
            $transaction = $this->paymentGateway->processCreditCard($paymentData);

            // Salvar dados adicionais
            $transaction->user_id = $user->id;
            $transaction->created_by = Auth::id();
            $transaction->save();

            DB::commit();

            // Log da atividade
            activity()
                ->performedOn($transaction)
                ->causedBy(Auth::user())
                ->withProperties([
                    'amount' => $transaction->amount,
                    'installments' => $transaction->installments,
                    'card_brand' => $transaction->card_brand,
                ])
                ->log('Transação de cartão processada');

            return redirect()
                ->route('financeiro.credit-cards.show', $transaction)
                ->with('success', 'Transação processada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao processar transação', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
        }
    }

    /**
     * Process payment (API/AJAX).
     */
    public function processPayment(Request $request): RedirectResponse
    {
        // Redireciona para o mesmo método store
        return $this->store($request);
    }

    /**
     * Display the specified transaction.
     */
    public function show(CreditCardTransaction $transaction): View
    {
        $transaction->load(['user', 'createdBy']);

        return view('financeiro.credit-cards.show', compact('transaction'));
    }

    /**
     * Process refund.
     */
    public function refund(Request $request, CreditCardTransaction $transaction): RedirectResponse
    {
        if (!$transaction->canBeRefunded()) {
            return back()->with('error', 'Esta transação não pode ser reembolsada.');
        }

        $validated = $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01', 'max:' . $transaction->amount],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::beginTransaction();

            $refundAmount = $validated['amount'] ?? $transaction->amount;
            $reason = $validated['reason'] ?? 'Reembolso solicitado pelo operador';

            // Processar reembolso no gateway
            $success = $this->paymentGateway->processRefund(
                $transaction->gateway_transaction_id,
                $refundAmount
            );

            if ($success) {
                $transaction->refund($refundAmount, $reason);
            }

            DB::commit();

            activity()
                ->performedOn($transaction)
                ->causedBy(Auth::user())
                ->withProperties(['refunded_amount' => $refundAmount, 'reason' => $reason])
                ->log('Transação reembolsada');

            return back()->with('success', 'Reembolso processado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erro ao processar reembolso', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);

            return back()->with('error', 'Erro ao processar reembolso: ' . $e->getMessage());
        }
    }

    /**
     * Generate transaction receipt PDF.
     */
    public function generateReceipt(CreditCardTransaction $transaction)
    {
        try {
            $transaction->load('user');

            $pdf = PDF::loadView('financeiro.credit-cards.receipt', [
                'transaction' => $transaction,
                'company' => [
                    'name' => config('app.name'),
                    'cnpj' => config('app.company_cnpj', '00.000.000/0000-00'),
                ],
                'generated_at' => now()->format('d/m/Y H:i:s'),
            ]);

            return $pdf->download('comprovante-' . $transaction->transaction_id . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erro ao gerar comprovante', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id,
            ]);

            return back()->with('error', 'Erro ao gerar comprovante.');
        }
    }

    /**
     * Get transaction statistics.
     */
    private function getStats(): array
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        return [
            'today_count' => CreditCardTransaction::whereDate('created_at', $today)->count(),
            'today_amount' => CreditCardTransaction::whereDate('created_at', $today)->sum('amount'),
            'month_count' => CreditCardTransaction::whereDate('created_at', '>=', $monthStart)->count(),
            'month_amount' => CreditCardTransaction::whereDate('created_at', '>=', $monthStart)->sum('amount'),
            'approved_count' => CreditCardTransaction::where('status', 'approved')
                ->whereDate('created_at', '>=', $monthStart)->count(),
            'average_ticket' => CreditCardTransaction::whereDate('created_at', '>=', $monthStart)->avg('amount') ?? 0,
        ];
    }
}
