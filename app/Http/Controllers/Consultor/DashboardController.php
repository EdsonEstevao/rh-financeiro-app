<?php
// app/Http/Controllers/Consultor/DashboardController.php

namespace App\Http\Controllers\Consultor;

use Illuminate\Http\{JsonResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{Auth, Cache};
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\{Boleto, CreditCardTransaction, Department, Employee, User};

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|consultor']);
        $this->middleware('permission:consultor.dashboard.view');
    }

    /**
     * Display the consultant dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Cache dashboard data for 5 minutes to improve performance
        $cacheKey = "consultor_dashboard_{$user->id}";

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user) {
            return [
                'stats' => $this->getStatistics(),
                'recentClients' => $this->getRecentClients(),
                'upcomingBoletos' => $this->getUpcomingBoletos(),
                'monthlyPerformance' => $this->getMonthlyPerformance(),
                'clientActivitySummary' => $this->getClientActivitySummary(),
            ];
        });

        return view('consultor.dashboard', array_merge($data, [
            'user' => $user,
        ]));
    }

    /**
     * Display clients list.
     */
    public function clients(Request $request): View
    {
        $query = User::role('funcionario')
            ->with(['employee.department', 'boletos' => fn($q) => $q->latest()->limit(5)])
            ->withCount(['boletos', 'boletos as pending_boletos_count' => fn($q) => $q->where('status', 'pending')])
            ->withSum(['boletos as total_boletos_amount' => fn($q) => $q->where('status', 'pending')], 'amount');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Apply department filter
        if ($request->filled('department')) {
            $query->whereHas('employee', fn($q) => $q->where('department_id', $request->department));
        }

        $clients = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $departments = Department::orderBy('name')->get();

        return view('consultor.clients', compact('clients', 'departments'));
    }

    /**
     * Show client details.
     */
    public function showClient(User $user): View
    {
        // Ensure the user has the funcionario role
        if (!$user->hasRole('funcionario')) {
            abort(404);
        }

        $user->load([
            'employee.department',
            'employee.supervisor.user',
            'boletos' => fn($q) => $q->latest()->take(20),
            'creditCardTransactions' => fn($q) => $q->latest()->take(20),
        ]);

        $clientStats = [
            'total_boletos' => $user->boletos()->count(),
            'paid_boletos' => $user->boletos()->where('status', 'paid')->count(),
            'pending_boletos' => $user->boletos()->where('status', 'pending')->count(),
            'overdue_boletos' => $user->boletos()->where('status', 'overdue')->count(),
            'total_paid_amount' => $user->boletos()->where('status', 'paid')->sum('amount'),
            'total_pending_amount' => $user->boletos()->where('status', 'pending')->sum('amount'),
            'credit_card_transactions' => $user->creditCardTransactions()->count(),
            'credit_card_volume' => $user->creditCardTransactions()->where('status', 'approved')->sum('amount'),
        ];

        return view('consultor.clients-show', compact('user', 'clientStats'));
    }

    /**
     * Get dashboard statistics.
     */
    private function getStatistics(): array
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $yearStart = $now->copy()->startOfYear();

        // Client statistics
        $allClients = User::role('funcionario');
        $activeClients = (clone $allClients)->where('is_active', true);
        $newClientsThisMonth = (clone $allClients)
            ->where('created_at', '>=', $monthStart);
        $newClientsThisYear = (clone $allClients)
            ->where('created_at', '>=', $yearStart);

        // Boleto statistics
        $allBoletos = Boleto::query();
        $pendingBoletos = (clone $allBoletos)->where('status', 'pending');
        $paidThisMonth = (clone $allBoletos)
            ->where('status', 'paid')
            ->where('paid_at', '>=', $monthStart);

        // Credit card statistics
        $cardTransactions = CreditCardTransaction::query();
        $cardVolumeMonth = (clone $cardTransactions)
            ->where('status', 'approved')
            ->where('created_at', '>=', $monthStart);

        // Calculate commission (example: 5%)
        $commissionRate = config('commissions.default_rate', 5);
        $totalRevenue = $paidThisMonth->sum('amount') + $cardVolumeMonth->sum('amount');
        $estimatedCommission = $totalRevenue * ($commissionRate / 100);

        return [
            'total_clients' => $allClients->count(),
            'active_clients' => $activeClients->count(),
            'new_clients_this_month' => $newClientsThisMonth->count(),
            'new_clients_this_year' => $newClientsThisYear->count(),
            'active_rate' => $allClients->count() > 0
                ? round(($activeClients->count() / $allClients->count()) * 100)
                : 0,

            'total_boletos' => $allBoletos->count(),
            'pending_boletos' => $pendingBoletos->count(),
            'pending_boletos_amount' => $pendingBoletos->sum('amount'),
            'paid_this_month' => $paidThisMonth->count(),
            'paid_this_month_amount' => $paidThisMonth->sum('amount'),

            'card_volume_month' => $cardVolumeMonth->sum('amount'),
            'card_transactions_month' => $cardVolumeMonth->count(),

            'commission_rate' => $commissionRate,
            'estimated_commission' => $estimatedCommission,
            'total_revenue' => $totalRevenue,
        ];
    }

    /**
     * Get recent clients.
     */
    private function getRecentClients(): \Illuminate\Database\Eloquent\Collection
    {
        return User::role('funcionario')
            ->with(['employee.department'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($client) {
                $client->pending_amount = $client->boletos()
                    ->where('status', 'pending')
                    ->sum('amount');
                return $client;
            });
    }

    /**
     * Get upcoming boletos (due in next 15 days).
     */
    private function getUpcomingBoletos(): \Illuminate\Database\Eloquent\Collection
    {
        return Boleto::with('user')
            ->where('status', 'pending')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(15))
            ->orderBy('due_date')
            ->take(10)
            ->get()
            ->map(function ($boleto) {
                $boleto->days_until_due = now()->diffInDays($boleto->due_date, false);
                return $boleto;
            });
    }

    /**
     * Get monthly performance data.
     */
    private function getMonthlyPerformance(): array
    {
        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // Current month
        $currentMonthBoletos = Boleto::where('created_at', '>=', $monthStart);
        $currentMonthCards = CreditCardTransaction::where('created_at', '>=', $monthStart);

        // Last month for comparison
        $lastMonthBoletos = Boleto::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd]);
        $lastMonthCards = CreditCardTransaction::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd]);

        return [
            'boleto_stats' => [
                'issued' => $currentMonthBoletos->count(),
                'issued_last_month' => $lastMonthBoletos->count(),
                'paid' => (clone $currentMonthBoletos)->where('status', 'paid')->count(),
                'paid_last_month' => (clone $lastMonthBoletos)->where('status', 'paid')->count(),
                'pending' => (clone $currentMonthBoletos)->where('status', 'pending')->count(),
                'overdue' => (clone $currentMonthBoletos)->where('status', 'overdue')->count(),
                'amount' => $currentMonthBoletos->sum('amount'),
                'amount_last_month' => $lastMonthBoletos->sum('amount'),
                'growth' => $this->calculateGrowth(
                    $lastMonthBoletos->sum('amount'),
                    $currentMonthBoletos->sum('amount')
                ),
            ],
            'card_stats' => [
                'transactions' => $currentMonthCards->count(),
                'transactions_last_month' => $lastMonthCards->count(),
                'approved' => (clone $currentMonthCards)->where('status', 'approved')->count(),
                'volume' => $currentMonthCards->sum('amount'),
                'volume_last_month' => $lastMonthCards->sum('amount'),
                'average_ticket' => $currentMonthCards->count() > 0
                    ? $currentMonthCards->avg('amount')
                    : 0,
                'growth' => $this->calculateGrowth(
                    $lastMonthCards->sum('amount'),
                    $currentMonthCards->sum('amount')
                ),
            ],
            'client_stats' => [
                'new_this_month' => User::role('funcionario')
                    ->where('created_at', '>=', $monthStart)->count(),
                'new_last_month' => User::role('funcionario')
                    ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count(),
                'with_boletos' => User::role('funcionario')
                    ->whereHas('boletos', fn($q) => $q->where('created_at', '>=', $monthStart))->count(),
                'defaulting' => User::role('funcionario')
                    ->whereHas('boletos', fn($q) => $q->where('status', 'overdue')
                        ->orWhere(fn($sq) => $sq->where('status', 'pending')->where('due_date', '<', now()))
                    )->count(),
            ],
        ];
    }

    /**
     * Get client activity summary.
     */
    private function getClientActivitySummary(): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        return [
            'most_active_clients' => User::role('funcionario')
                ->withCount(['boletos' => fn($q) => $q->where('created_at', '>=', $thirtyDaysAgo)])
                ->orderByDesc('boletos_count')
                ->take(5)
                ->get()
                ->map(fn($user) => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'boletos_count' => $user->boletos_count,
                ]),

            'highest_value_clients' => User::role('funcionario')
                ->withSum(['boletos as total_amount' => fn($q) => $q->where('created_at', '>=', $thirtyDaysAgo)], 'amount')
                ->orderByDesc('total_amount')
                ->take(5)
                ->get()
                ->map(fn($user) => [
                    'name' => $user->name,
                    'total_amount' => $user->total_amount,
                ]),

            'inactive_clients' => User::role('funcionario')
                ->where('is_active', true)
                ->whereDoesntHave('boletos', fn($q) => $q->where('created_at', '>=', $thirtyDaysAgo))
                ->count(),
        ];
    }

    /**
     * Calculate growth percentage between two values.
     */
    private function calculateGrowth(float $previous, float $current): ?float
    {
        if ($previous == 0 && $current == 0) {
            return null;
        }

        if ($previous == 0) {
            return 100;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * API: Get dashboard stats as JSON.
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->getStatistics(),
        ]);
    }

    /**
     * API: Search clients.
     */
    public function searchClients(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $clients = User::role('funcionario')
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%");
            })
            ->where('is_active', true)
            ->limit(10)
            ->get(['id', 'name', 'email', 'cpf']);

        return response()->json([
            'success' => true,
            'data' => $clients,
        ]);
    }
}
