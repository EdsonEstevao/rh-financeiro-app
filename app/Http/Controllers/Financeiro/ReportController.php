<?php
// app/Http/Controllers/Financeiro/ReportController.php

namespace App\Http\Controllers\Financeiro;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\{Boleto, CreditCardTransaction, User};
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {
        $this->middleware(['auth', 'role:admin|financeiro']);
        $this->middleware('permission:financeiro.reports.view');
    }

    /**
     * Display reports index.
     */
    public function index(): View
    {
        $reportTypes = [
            ['id' => 'boletos', 'name' => 'Relatório de Boletos', 'icon' => 'document-text', 'description' => 'Relatório detalhado de boletos'],
            ['id' => 'credit-cards', 'name' => 'Transações Cartão', 'icon' => 'credit-card', 'description' => 'Relatório de transações de cartão'],
            ['id' => 'receivables', 'name' => 'Contas a Receber', 'icon' => 'trending-up', 'description' => 'Relatório de contas a receber'],
            ['id' => 'cash-flow', 'name' => 'Fluxo de Caixa', 'icon' => 'chart-bar', 'description' => 'Relatório de fluxo de caixa'],
            ['id' => 'dailies', 'name' => 'Fechamento Diário', 'icon' => 'calendar', 'description' => 'Relatório de fechamento diário'],
            ['id' => 'commissions', 'name' => 'Comissões', 'icon' => 'percent', 'description' => 'Relatório de comissões'],
        ];

        return view('financeiro.reports.index', compact('reportTypes'));
    }

    /**
     * Boletos report.
     */
    public function boletos(Request $request): View
    {
        $query = Boleto::with('user')
            ->when($request->filled('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->filled('date_from'), fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->filled('date_to'), fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($request->filled('client'), fn($q, $c) => $q->where('user_id', $c))
            ->orderBy('created_at', 'desc');

        $boletos = $query->get();

        $summary = [
            'total_count' => $boletos->count(),
            'total_amount' => $boletos->sum('amount'),
            'paid_count' => $boletos->where('status', 'paid')->count(),
            'paid_amount' => $boletos->where('status', 'paid')->sum('amount'),
            'pending_count' => $boletos->where('status', 'pending')->count(),
            'pending_amount' => $boletos->where('status', 'pending')->sum('amount'),
            'overdue_count' => $boletos->where('status', 'overdue')->count(),
            'overdue_amount' => $boletos->where('status', 'overdue')->sum('amount'),
            'cancelled_count' => $boletos->where('status', 'cancelled')->count(),
        ];

        $clients = User::whereHas('boletos')->orderBy('name')->get();

        return view('financeiro.reports.boletos', compact('boletos', 'summary', 'clients'));
    }

    /**
     * Download boletos PDF.
     */
    public function downloadBoletosPDF(Request $request)
    {
        $boletos = Boleto::with('user')
            ->when($request->filled('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->filled('date_from'), fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->filled('date_to'), fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderBy('created_at', 'desc')
            ->get();

        $data = [
            'boletos' => $boletos,
            'filters' => [
                'status' => $request->status ?: 'Todos',
                'period' => $request->date_from && $request->date_to
                    ? Carbon::parse($request->date_from)->format('d/m/Y') . ' até ' . Carbon::parse($request->date_to)->format('d/m/Y')
                    : 'Todo período',
                'date' => now()->format('d/m/Y H:i')
            ],
            'summary' => [
                'total_amount' => $boletos->sum('amount'),
                'paid_amount' => $boletos->where('status', 'paid')->sum('amount'),
                'pending_amount' => $boletos->where('status', 'pending')->sum('amount'),
                'total_count' => $boletos->count(),
            ]
        ];

        return $this->reportService->downloadPDF(
            'financeiro.reports.pdfs.boletos',
            $data,
            'relatorio-boletos-' . now()->format('d-m-Y') . '.pdf',
            'landscape'
        );
    }

    /**
     * Stream boletos PDF.
     */
    public function streamBoletosPDF(Request $request)
    {
        $boletos = Boleto::with('user')
            ->when($request->filled('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->filled('date_from'), fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->filled('date_to'), fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->reportService->streamPDF(
            'financeiro.reports.pdfs.boletos',
            ['boletos' => $boletos],
            'relatorio-boletos.pdf',
            'landscape'
        );
    }

    /**
     * Credit cards report.
     */
    public function creditCards(Request $request): View
    {
        $query = CreditCardTransaction::with('user')
            ->when($request->filled('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->filled('date_from'), fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->filled('date_to'), fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderBy('created_at', 'desc');

        $transactions = $query->get();

        $byCardBrand = $transactions->groupBy('card_brand')->map(fn($group) => [
            'count' => $group->count(),
            'total' => $group->sum('amount')
        ]);

        $summary = [
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'approved_amount' => $transactions->where('status', 'approved')->sum('amount'),
            'rejected_amount' => $transactions->where('status', 'rejected')->sum('amount'),
            'average_ticket' => $transactions->count() > 0 ? $transactions->avg('amount') : 0,
        ];

        return view('financeiro.reports.credit-cards', compact('transactions', 'byCardBrand', 'summary'));
    }

    /**
     * Download credit cards PDF.
     */
    public function downloadCreditCardsPDF(Request $request)
    {
        $transactions = CreditCardTransaction::with('user')
            ->when($request->filled('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->filled('date_from'), fn($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->filled('date_to'), fn($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->reportService->downloadPDF(
            'financeiro.reports.pdfs.credit-cards',
            ['transactions' => $transactions],
            'relatorio-cartoes-' . now()->format('d-m-Y') . '.pdf',
            'landscape'
        );
    }

    /**
     * Cash flow report.
     */
    public function cashFlow(Request $request): View
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        // Entradas (boletos pagos + cartão aprovado)
        $paidBoletos = Boleto::where('status', 'paid')
            ->whereMonth('paid_at', $month)
            ->whereYear('paid_at', $year)
            ->get()
            ->map(fn($b) => [
                'date' => $b->paid_at->format('Y-m-d'),
                'description' => 'Boleto #' . $b->id . ' - ' . $b->payer_name,
                'amount' => $b->amount,
                'type' => 'Boleto'
            ]);

        $creditCards = CreditCardTransaction::where('status', 'approved')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get()
            ->map(fn($t) => [
                'date' => $t->created_at->format('Y-m-d'),
                'description' => 'Cartão #' . $t->transaction_id . ' - ' . $t->customer_name,
                'amount' => $t->net_amount,
                'type' => 'Cartão'
            ]);

        $incomes = $paidBoletos->concat($creditCards)->sortBy('date');
        $dailyBalance = $incomes->groupBy('date')->map->sum('amount');
        $totalIncome = $incomes->sum('amount');

        return view('financeiro.reports.cash-flow', compact(
            'incomes', 'dailyBalance', 'totalIncome', 'month', 'year'
        ));
    }

    /**
     * Receivables report.
     */
    public function receivables(Request $request): View
    {
        $query = Boleto::with('user')
            ->whereIn('status', ['pending', 'overdue'])
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                  ->where('due_date', '<', now());
            })
            ->orderBy('due_date');

        $receivables = $query->get();

        $agingSummary = [
            'current' => $receivables->filter(fn($b) => $b->due_date->isFuture())->sum('amount'),
            '1_30_days' => $receivables->filter(fn($b) => $b->due_date->isPast() && $b->due_date->diffInDays(now()) <= 30)->sum('amount'),
            '31_60_days' => $receivables->filter(fn($b) => $b->due_date->diffInDays(now()) > 30 && $b->due_date->diffInDays(now()) <= 60)->sum('amount'),
            '61_90_days' => $receivables->filter(fn($b) => $b->due_date->diffInDays(now()) > 60 && $b->due_date->diffInDays(now()) <= 90)->sum('amount'),
            '90_plus_days' => $receivables->filter(fn($b) => $b->due_date->diffInDays(now()) > 90)->sum('amount'),
        ];

        return view('financeiro.reports.receivables', compact('receivables', 'agingSummary'));
    }

    /**
     * Daily closing report.
     */
    public function dailies(Request $request): View
    {
        $date = $request->date ? Carbon::parse($request->date) : today();

        $dailyBoletos = Boleto::whereDate('created_at', $date)->get();
        $dailyCards = CreditCardTransaction::whereDate('created_at', $date)->get();

        $summary = [
            'total_sales' => $dailyBoletos->sum('amount') + $dailyCards->sum('amount'),
            'boleto_count' => $dailyBoletos->count(),
            'card_count' => $dailyCards->count(),
            'boleto_amount' => $dailyBoletos->sum('amount'),
            'card_amount' => $dailyCards->sum('amount'),
            'paid_boletos' => $dailyBoletos->where('status', 'paid')->count(),
            'approved_cards' => $dailyCards->where('status', 'approved')->count(),
        ];

        return view('financeiro.reports.dailies', compact('date', 'summary', 'dailyBoletos', 'dailyCards'));
    }

    /**
     * Commissions report.
     */
    public function commissions(Request $request): View
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $commissionRate = config('commissions.default_rate', 5); // 5%

        $consultants = User::role(['consultor', 'gerente'])
            ->withSum(['boletos as total_boletos' => function ($q) use ($month, $year) {
                $q->where('status', 'paid')
                  ->whereMonth('paid_at', $month)
                  ->whereYear('paid_at', $year);
            }], 'amount')
            ->get()
            ->map(function ($user) use ($commissionRate) {
                $user->commission = $user->total_boletos * ($commissionRate / 100);
                return $user;
            });

        return view('financeiro.reports.commissions', compact('consultants', 'month', 'year', 'commissionRate'));
    }

    /**
     * API: Get stats for AJAX calls.
     */
    public function stats(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'pending_boletos' => Boleto::where('status', 'pending')->count(),
            'today_income' => Boleto::where('status', 'paid')->whereDate('paid_at', today())->sum('amount'),
            'today_transactions' => CreditCardTransaction::whereDate('created_at', today())->count(),
        ]);
    }
}
