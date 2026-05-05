<?php
// app/Http/Controllers/Financeiro/ReportOldController.php

namespace App\Http\Controllers\Financeiro;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\{Boleto, CreditCardTransaction};
use App\Services\ReportService;

class ReportOldController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {
        $this->middleware(['auth', 'role:admin|financeiro']);
    }

    public function index(): View
    {
        $reportTypes = [
            [
                'id' => 'boletos',
                'name' => 'Relatório de Boletos',
                'icon' => 'document-text',
                'description' => 'Relatório detalhado de boletos emitidos',
                'filters' => ['period', 'status', 'client']
            ],
            [
                'id' => 'credit-cards',
                'name' => 'Transações Cartão',
                'icon' => 'credit-card',
                'description' => 'Relatório de transações de cartão de crédito',
                'filters' => ['period', 'status', 'client']
            ],
            [
                'id' => 'receivables',
                'name' => 'Contas a Receber',
                'icon' => 'trending-up',
                'description' => 'Relatório de contas a receber',
                'filters' => ['period', 'status']
            ],
            [
                'id' => 'cash-flow',
                'name' => 'Fluxo de Caixa',
                'icon' => 'chart-bar',
                'description' => 'Relatório de fluxo de caixa',
                'filters' => ['month', 'year']
            ],
            [
                'id' => 'dailies',
                'name' => 'Fechamento Diário',
                'icon' => 'calendar',
                'description' => 'Relatório de fechamento diário',
                'filters' => ['date']
            ],
            [
                'id' => 'commissions',
                'name' => 'Comissões',
                'icon' => 'percent',
                'description' => 'Relatório de comissões por vendedor/consultor',
                'filters' => ['period', 'consultant']
            ]
        ];

        return view('financeiro.reports.index', compact('reportTypes'));
    }

    // Relatório de Boletos
    public function boletos(Request $request): View
    {
        $query = Boleto::with('user')
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->client, function ($query, $client) {
                $query->where('user_id', $client);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            });

        $boletos = $query->orderBy('created_at', 'desc')->get();

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

        $clients = \App\Models\User::whereHas('roles', function ($q) {
            $q->where('name', 'funcionario');
        })->orderBy('name')->get();

        return view('financeiro.reports.boletos', compact('boletos', 'summary', 'clients'));
    }

    public function downloadBoletosPDF(Request $request)
    {
        $data = $this->getBoletosReportData($request);

        return $this->reportService->downloadPDF(
            'financeiro.reports.pdfs.boletos',
            $data,
            'relatorio-boletos-' . now()->format('d-m-Y') . '.pdf',
            'landscape'
        );
    }

    public function streamBoletosPDF(Request $request)
    {
        $data = $this->getBoletosReportData($request);

        return $this->reportService->streamPDF(
            'financeiro.reports.pdfs.boletos',
            $data,
            'relatorio-boletos-' . now()->format('d-m-Y') . '.pdf',
            'landscape'
        );
    }

    private function getBoletosReportData(Request $request): array
    {
        $boletos = Boleto::with('user')
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'boletos' => $boletos,
            'filters' => [
                'status' => $request->status ?: 'Todos',
                'period' => $request->date_from && $request->date_to ?
                    $this->reportService->formatDate($request->date_from) . ' até ' .
                    $this->reportService->formatDate($request->date_to) :
                    'Todo período',
                'date' => now()->format('d/m/Y H:i')
            ],
            'summary' => [
                'total_amount' => $boletos->sum('amount'),
                'paid_amount' => $boletos->where('status', 'paid')->sum('amount'),
                'pending_amount' => $boletos->where('status', 'pending')->sum('amount'),
                'total_count' => $boletos->count(),
            ]
        ];
    }

    // Relatório de Cartão de Crédito
    public function creditCards(Request $request): View
    {
        $transactions = CreditCardTransaction::with('user')
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('amount'),
            'approved_amount' => $transactions->where('status', 'approved')->sum('amount'),
            'rejected_amount' => $transactions->where('status', 'rejected')->sum('amount'),
            'average_ticket' => $transactions->count() > 0 ?
                $transactions->sum('amount') / $transactions->count() : 0,
        ];

        return view('financeiro.reports.credit-cards', compact('transactions', 'summary'));
    }

    public function downloadCreditCardsPDF(Request $request)
    {
        $data = $this->getCreditCardsReportData($request);

        return $this->reportService->downloadPDF(
            'financeiro.reports.pdfs.credit-cards',
            $data,
            'relatorio-cartoes-' . now()->format('d-m-Y') . '.pdf',
            'landscape'
        );
    }

    private function getCreditCardsReportData(Request $request): array
    {
        $transactions = CreditCardTransaction::with('user')
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $byCardBrand = $transactions->groupBy('card_brand')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('amount')
            ];
        });

        return [
            'transactions' => $transactions,
            'byCardBrand' => $byCardBrand,
            'filters' => [
                'status' => $request->status ?: 'Todos',
                'period' => $request->date_from && $request->date_to ?
                    $this->reportService->formatDate($request->date_from) . ' até ' .
                    $this->reportService->formatDate($request->date_to) :
                    'Todo período',
                'date' => now()->format('d/m/Y H:i')
            ],
            'summary' => [
                'total_transactions' => $transactions->count(),
                'total_amount' => $transactions->sum('amount'),
                'approved_amount' => $transactions->where('status', 'approved')->sum('amount'),
                'average_ticket' => $transactions->count() > 0 ?
                    $transactions->sum('amount') / $transactions->count() : 0,
            ]
        ];
    }

    // Dashboard de Fluxo de Caixa
    public function cashFlow(Request $request): View
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        // Entradas (Boletos pagos + Cartão aprovado)
        $incomes = collect();

        $paidBoletos = Boleto::where('status', 'paid')
            ->whereMonth('paid_at', $month)
            ->whereYear('paid_at', $year)
            ->get()
            ->map(function ($boleto) {
                return [
                    'date' => $boleto->paid_at->format('Y-m-d'),
                    'description' => 'Boleto #' . $boleto->id,
                    'amount' => $boleto->amount,
                    'type' => 'Boleto'
                ];
            });

        $creditCardApproved = CreditCardTransaction::where('status', 'approved')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get()
            ->map(function ($transaction) {
                return [
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'description' => 'Cartão #' . $transaction->id,
                    'amount' => $transaction->amount,
                    'type' => 'Cartão de Crédito'
                ];
            });

        $incomes = $paidBoletos->concat($creditCardApproved)->sortBy('date');

        $dailyBalance = $incomes->groupBy('date')->map(function ($day) {
            return $day->sum('amount');
        });

        $totalIncome = $incomes->sum('amount');
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        return view('financeiro.reports.cash-flow', compact(
            'incomes',
            'dailyBalance',
            'totalIncome',
            'month',
            'year',
            'daysInMonth'
        ));
    }

    public function downloadCashFlowPDF(Request $request)
    {
        // Similar aos anteriores, preparar dados e retornar PDF
        $data = $this->getCashFlowData($request);

        return $this->reportService->downloadPDF(
            'financeiro.reports.pdfs.cash-flow',
            $data,
            'fluxo-caixa-' . $request->month . '-' . $request->year . '.pdf'
        );
    }

    private function getCashFlowData(Request $request): array
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        // Mesma lógica do método cashFlow()
        $paidBoletos = Boleto::where('status', 'paid')
            ->whereMonth('paid_at', $month)
            ->whereYear('paid_at', $year)
            ->sum('amount');

        $creditCardApproved = CreditCardTransaction::where('status', 'approved')
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->sum('amount');

        return [
            'month' => $month,
            'year' => $year,
            'total_boletos' => $paidBoletos,
            'total_credit_cards' => $creditCardApproved,
            'total_income' => $paidBoletos + $creditCardApproved,
            'filters' => [
                'period' => Carbon::createFromDate($year, $month, 1)->format('m/Y'),
                'date' => now()->format('d/m/Y H:i')
            ]
        ];
    }
}