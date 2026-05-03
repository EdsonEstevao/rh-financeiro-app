<?php
// app/Http/Controllers/Financeiro/CreditCardController.php

namespace App\Http\Controllers\Financeiro;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\{CreditCardTransaction, User};
use App\Services\PaymentGateway;
use Barryvdh\DomPDF\Facade\Pdf;

class CreditCardController extends Controller
{
    public function __construct(
        private readonly PaymentGateway $paymentGateway
    ) {
        $this->middleware(['auth', 'role:admin|financeiro|consultor']);

        $this->middleware('permission:financeiro.credit-cards.process')->only(['processPayment']);
        $this->middleware('permission:financeiro.credit-cards.refund')->only(['refund']);
    }

    /**
     * Display a listing of credit card transactions.
     */
    public function index(Request $request): View
    {
        $query = CreditCardTransaction::with('user')
            ->latest();

        // Aplicar filtros
        $this->applyFilters($query, $request);

        $transactions = $query->paginate(15)->withQueryString();

        // Estatísticas
        $stats = $this->getStats();

        return view('financeiro.credit-cards.index', compact('transactions', 'stats'));
    }

    /**
     * Display the specified transaction.
     */
    public function show(CreditCardTransaction $transaction): View
    {
        $transaction->load('user');

        return view('financeiro.credit-cards.show', compact('transaction'));
    }

    /**
     * Process a new credit card payment.
     */
    public function processPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'installments' => ['required', 'integer', 'min:1', 'max:12'],
            'card_number' => ['required', 'string', 'min:13', 'max:19'],
            'card_holder_name' => ['required', 'string', 'max:200'],
            'card_expiration_month' => ['required', 'integer', 'min:1', 'max:12'],
            'card_expiration_year' => ['required', 'integer', 'min:' . date('Y'), 'max:' . (date('Y') + 20)],
            'card_cvv' => ['required', 'string', 'min:3', 'max:4'],
            'description' => ['required', 'string', 'max:500'],
            'customer_email' => ['required', 'email'],
        ]);

        try {
            DB::beginTransaction();

            // Simular processamento de pagamento
            $transactionData = [
                'user_id' => $validated['user_id'],
                'transaction_id' => CreditCardTransaction::generateTransactionId(),
                'amount' => $validated['amount'],
                'original_amount' => $validated['amount'],
                'fee_amount' => $validated['amount'] * 0.03, // 3% taxa
                'net_amount' => $validated['amount'] * 0.97,
                'installments' => $validated['installments'],
                'installment_amount' => $validated['amount'] / $validated['installments'],
                'is_installment' => $validated['installments'] > 1,
                'card_holder_name' => $validated['card_holder_name'],
                'card_last_digits' => substr($validated['card_number'], -4),
                'card_bin' => substr($validated['card_number'], 0, 6),
                'card_brand' => $this->detectCardBrand($validated['card_number']),
                'card_type' => 'credit',
                'customer_name' => User::find($validated['user_id'])->name,
                'customer_email' => $validated['customer_email'],
                'customer_document' => User::find($validated['user_id'])->cpf,
                'description' => $validated['description'],
                'status' => 'approved', // Mock: sempre aprova
                'authorization_code' => strtoupper(substr(md5(uniqid()), 0, 10)),
                'authorized_at' => now(),
                'created_by' => auth()->id(),
                'gateway' => 'simulation',
            ];

            $transaction = CreditCardTransaction::create($transactionData);

            DB::commit();

            // Log da atividade
            activity()
                ->performedOn($transaction)
                ->causedBy(auth()->user())
                ->withProperties([
                    'amount' => $transaction->amount,
                    'installments' => $transaction->installments,
                    'card_brand' => $transaction->card_brand,
                ])
                ->log('Transação de cartão processada');

            Log::info('Credit card transaction processed', [
                'transaction_id' => $transaction->id,
                'amount' => $transaction->amount
            ]);

            return redirect()
                ->route('financeiro.credit-cards.show', $transaction)
                ->with('success', 'Transação processada com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to process credit card transaction', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Erro ao processar pagamento. Por favor, tente novamente.');
        }
    }

    /**
     * Process a refund for a transaction.
     */
    public function refund(CreditCardTransaction $transaction, Request $request): RedirectResponse
    {
        if (!$transaction->canBeRefunded()) {
            return back()->with('error', 'Esta transação não pode ser reembolsada.');
        }

        $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01', 'max:' . $transaction->amount],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            DB::beginTransaction();

            $refundAmount = $request->amount ?? $transaction->amount;
            $reason = $request->reason ?? 'Reembolso solicitado pelo operador';

            $transaction->refund($refundAmount, $reason);

            DB::commit();

            activity()
                ->performedOn($transaction)
                ->causedBy(auth()->user())
                ->withProperties([
                    'refunded_amount' => $refundAmount,
                    'reason' => $reason,
                ])
                ->log('Transação reembolsada');

            return back()->with('success', 'Reembolso processado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to refund transaction', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ]);

            return back()->with('error', 'Erro ao processar reembolso.');
        }
    }

    /**
     * Generate transaction receipt.
     */
    public function generateReceipt(CreditCardTransaction $transaction)
    {
        try {
            $pdf = PDF::loadView('financeiro.credit-cards.receipt', [
                'transaction' => $transaction,
            ]);

            return $pdf->download("comprovante-{$transaction->transaction_id}.pdf");

        } catch (\Exception $e) {
            Log::error('Failed to generate receipt', [
                'error' => $e->getMessage(),
                'transaction_id' => $transaction->id
            ]);

            return back()->with('error', 'Erro ao gerar comprovante.');
        }
    }

    /**
     * Detect credit card brand by bin/number.
     */
    private function detectCardBrand(string $number): string
    {
        $number = preg_replace('/\D/', '', $number);

        return match (true) {
            preg_match('/^4/', $number) === 1 => 'visa',
            preg_match('/^5[1-5]/', $number) === 1 => 'mastercard',
            preg_match('/^3[47]/', $number) === 1 => 'amex',
            preg_match('/^(4011|4312|4389|4514|4576|5041|5066|6277|6363)/', $number) === 1 => 'elo',
            preg_match('/^(301|305|36|38)/', $number) === 1 => 'diners',
            preg_match('/^6/', $number) === 1 => 'discover',
            default => 'other',
        };
    }

    /**
     * Apply filters to query.
     */
    private function applyFilters($query, Request $request): void
    {
        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        $query->when($request->filled('card_brand'), function ($q) use ($request) {
            $q->where('card_brand', $request->card_brand);
        });

        $query->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->date_from);
        });

        $query->when($request->filled('date_to'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->date_to);
        });

        $query->when($request->filled('user_id'), function ($q) use ($request) {
            $q->where('user_id', $request->user_id);
        });

        $query->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($sq) use ($search) {
                $sq->where('transaction_id', 'like', "%{$search}%")
                   ->orWhere('authorization_code', 'like', "%{$search}%")
                   ->orWhere('customer_name', 'like', "%{$search}%")
                   ->orWhere('description', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Get statistics.
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
