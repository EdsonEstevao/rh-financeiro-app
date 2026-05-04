<?php
// app/Http/Controllers/Gerente/DashboardController.php

namespace App\Http\Controllers\Gerente;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\{Auth, Cache};
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\{Boleto, Department, Employee, EmployeeDocument, Payroll};

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|gerente']);
        $this->middleware('permission:gerente.dashboard.view');

        // Ensure manager has an employee record
        $this->middleware(function ($request, $next) {
            if (!Auth::user()->employee) {
                return redirect()->route('dashboard')
                    ->with('warning', 'Você não possui vínculo como gerente de departamento.');
            }
            return $next($request);
        });
    }

    /**
     * Display the manager dashboard.
     */
    public function index(Request $request): View
    {
        $manager = Auth::user();
        $department = $manager->employee?->department;

        if (!$department) {
            return view('gerente.dashboard', [
                'department' => null,
                'stats' => $this->getEmptyStats(),
            ])->with('warning', 'Você não está vinculado a um departamento.');
        }

        // Cache dashboard data
        $cacheKey = "gerente_dashboard_{$manager->id}_{$department->id}";

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($department, $manager) {
            return [
                'department' => $department,
                'stats' => $this->getDepartmentStats($department),
                'teamMembers' => $this->getTeamMembers($department),
                'upcomingEvents' => $this->getUpcomingEvents($department),
                'monthlySummary' => $this->getMonthlySummary($department),
                'pendingApprovals' => $this->getPendingApprovals($department),
            ];
        });

        return view('gerente.dashboard', array_merge($data, [
            'manager' => $manager,
        ]));
    }

    /**
     * Display team members.
     */
    public function team(Request $request): View
    {
        $department = Auth::user()->employee?->department;

        if (!$department) {
            abort(403, 'Você não está vinculado a um departamento.');
        }

        $query = Employee::with(['user', 'supervisor.user'])
            ->where('department_id', $department->id);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        $employees = $query->orderBy('position')
            ->paginate(15)
            ->withQueryString();

        $stats = $this->getTeamStats($department);

        return view('gerente.team', compact('employees', 'department', 'stats'));
    }

    /**
     * Show team member details.
     */
    public function showTeamMember(Employee $employee): View
    {
        // Check if manager can view this employee
        $this->authorizeTeamMemberAccess($employee);

        $employee->load([
            'user',
            'department',
            'supervisor.user',
            'documents' => fn($q) => $q->latest()->limit(10),
            'payrolls' => fn($q) => $q->latest()->limit(12),
        ]);

        $memberStats = [
            'total_payrolls' => $employee->payrolls()->count(),
            'last_salary' => $employee->payrolls()->latest()->first()?->net_salary,
            'pending_documents' => $employee->documents()->where('status', 'pending')->count(),
            'expiring_documents' => $employee->documents()
                ->where('expiration_date', '<=', now()->addDays(30))
                ->where('expiration_date', '>=', now())
                ->count(),
            'vacation_days' => $employee->vacation_days_available,
            'years_of_service' => $employee->years_of_service,
        ];

        return view('gerente.team-show', compact('employee', 'memberStats'));
    }

    /**
     * Get department statistics.
     */
    private function getDepartmentStats(Department $department): array
    {
        $employees = Employee::where('department_id', $department->id);
        $activeEmployees = (clone $employees)->where('status', 'active');
        $onVacation = (clone $employees)->where('status', 'vacation');
        $onLeave = (clone $employees)->whereIn('status', ['leave', 'suspended', 'inactive']);
        $terminated = (clone $employees)->where('status', 'terminated');

        $totalSalary = $activeEmployees->sum('salary');
        $avgSalary = $activeEmployees->count() > 0 ? $activeEmployees->avg('salary') : 0;

        return [
            'total_employees' => $employees->count(),
            'active_employees' => $activeEmployees->count(),
            'on_vacation' => $onVacation->count(),
            'on_leave' => $onLeave->count(),
            'terminated' => $terminated->count(),

            'total_salary' => $totalSalary,
            'average_salary' => $avgSalary,
            'budget' => $department->budget ?? 0,
            'budget_used_percentage' => $department->budget > 0
                ? round(($totalSalary / $department->budget) * 100, 1)
                : 0,

            'new_hires_this_month' => (clone $employees)
                ->whereMonth('hire_date', now()->month)
                ->whereYear('hire_date', now()->year)
                ->count(),
            'new_hires_this_year' => (clone $employees)
                ->whereYear('hire_date', now()->year)
                ->count(),
            'terminations_this_month' => (clone $employees)
                ->where('status', 'terminated')
                ->whereMonth('termination_date', now()->month)
                ->count(),

            'evaluated_count' => (clone $activeEmployees)
                ->whereNotNull('last_evaluation_date')
                ->count(),
            'avg_evaluation_score' => (clone $activeEmployees)
                ->whereNotNull('last_evaluation_score')
                ->avg('last_evaluation_score') ?? 0,
            'not_evaluated' => (clone $activeEmployees)
                ->whereNull('last_evaluation_date')
                ->count(),
        ];
    }

    /**
     * Get team members.
     */
    private function getTeamMembers(Department $department): \Illuminate\Database\Eloquent\Collection
    {
        return Employee::with(['user', 'supervisor.user'])
            ->where('department_id', $department->id)
            ->where('status', 'active')
            ->orderBy('position')
            ->take(10)
            ->get()
            ->map(function ($employee) {
                $employee->days_since_hire = $employee->hire_date->diffInDays(now());
                $employee->pending_docs = $employee->documents()
                    ->where('status', 'pending')
                    ->count();
                return $employee;
            });
    }

    /**
     * Get upcoming events (vacations, birthdays, probation endings).
     */
    private function getUpcomingEvents(Department $department): array
    {
        $employees = Employee::where('department_id', $department->id)
            ->where('status', 'active')
            ->with('user')
            ->get();

        $now = now();
        $thirtyDaysFromNow = $now->copy()->addDays(30);

        return [
            'upcoming_vacations' => $employees
                ->filter(fn($e) => $e->vacation_start_date
                    && $e->vacation_start_date->between($now, $thirtyDaysFromNow))
                ->sortBy('vacation_start_date')
                ->take(5)
                ->map(fn($e) => [
                    'name' => $e->user->name,
                    'start_date' => $e->vacation_start_date->format('d/m/Y'),
                    'end_date' => $e->vacation_end_date?->format('d/m/Y'),
                    'days_until' => $now->diffInDays($e->vacation_start_date),
                ])
                ->values(),

            'birthdays_this_month' => $employees
                ->filter(fn($e) => $e->birth_date
                    && $e->birth_date->format('m') == $now->format('m'))
                ->sortBy(fn($e) => $e->birth_date->format('d'))
                ->take(10)
                ->map(fn($e) => [
                    'name' => $e->user->name,
                    'birth_date' => $e->birth_date->format('d/m'),
                    'age' => $e->birth_date->age,
                ])
                ->values(),

            'probation_ending' => $employees
                ->filter(fn($e) => $e->probation_end_date
                    && $e->probation_end_date->between($now, $thirtyDaysFromNow))
                ->sortBy('probation_end_date')
                ->take(5)
                ->map(fn($e) => [
                    'name' => $e->user->name,
                    'end_date' => $e->probation_end_date->format('d/m/Y'),
                    'days_remaining' => $now->diffInDays($e->probation_end_date),
                ])
                ->values(),
        ];
    }

    /**
     * Get monthly summary.
     */
    private function getMonthlySummary(Department $department): array
    {
        $monthStart = now()->startOfMonth();
        $employeeIds = Employee::where('department_id', $department->id)->pluck('id');

        return [
            'new_hires' => Employee::where('department_id', $department->id)
                ->where('hire_date', '>=', $monthStart)
                ->count(),

            'terminations' => Employee::where('department_id', $department->id)
                ->where('status', 'terminated')
                ->where('termination_date', '>=', $monthStart)
                ->count(),

            'documents_pending' => EmployeeDocument::whereIn('employee_id', $employeeIds)
                ->where('status', 'pending')
                ->count(),

            'documents_expiring' => EmployeeDocument::whereIn('employee_id', $employeeIds)
                ->where('expiration_date', '<=', now()->addDays(30))
                ->where('expiration_date', '>=', now())
                ->count(),

            'evaluations_pending' => Employee::where('department_id', $department->id)
                ->where('status', 'active')
                ->whereNull('last_evaluation_date')
                ->count(),

            'payroll_processed' => Payroll::whereIn('employee_id', $employeeIds)
                ->where('year', now()->year)
                ->where('month', now()->month)
                ->count(),

            'payroll_total' => Payroll::whereIn('employee_id', $employeeIds)
                ->where('year', now()->year)
                ->where('month', now()->month)
                ->sum('net_salary'),
        ];
    }

    /**
     * Get pending approvals.
     */
    private function getPendingApprovals(Department $department): array
    {
        $employeeIds = Employee::where('department_id', $department->id)->pluck('id');

        return [
            'documents' => EmployeeDocument::whereIn('employee_id', $employeeIds)
                ->where('status', 'pending')
                ->with('employee.user')
                ->latest()
                ->take(10)
                ->get(),

            'vacations' => Employee::where('department_id', $department->id)
                ->where('status', 'vacation')
                ->where('vacation_start_date', '>=', now())
                ->with('user')
                ->orderBy('vacation_start_date')
                ->take(5)
                ->get(),
        ];
    }

    /**
     * Get team statistics.
     */
    private function getTeamStats(Department $department): array
    {
        $employees = Employee::where('department_id', $department->id);

        return [
            'total' => $employees->count(),
            'active' => (clone $employees)->where('status', 'active')->count(),
            'on_vacation' => (clone $employees)->where('status', 'vacation')->count(),
            'on_leave' => (clone $employees)->whereIn('status', ['leave', 'suspended'])->count(),
            'by_education' => (clone $employees)
                ->where('status', 'active')
                ->selectRaw('education_level, COUNT(*) as count')
                ->groupBy('education_level')
                ->get()
                ->mapWithKeys(fn($item) => [$item->education_level ?? 'not_informed' => $item->count]),
            'by_employment_type' => (clone $employees)
                ->selectRaw('employment_type, COUNT(*) as count')
                ->groupBy('employment_type')
                ->get()
                ->mapWithKeys(fn($item) => [$item->employment_type => $item->count]),
        ];
    }

    /**
     * Get empty stats fallback.
     */
    private function getEmptyStats(): array
    {
        return [
            'total_employees' => 0,
            'active_employees' => 0,
            'on_vacation' => 0,
            'on_leave' => 0,
            'terminated' => 0,
            'total_salary' => 0,
            'average_salary' => 0,
            'budget' => 0,
            'budget_used_percentage' => 0,
            'new_hires_this_month' => 0,
            'new_hires_this_year' => 0,
            'terminations_this_month' => 0,
            'evaluated_count' => 0,
            'avg_evaluation_score' => 0,
            'not_evaluated' => 0,
        ];
    }

    /**
     * Authorize team member access.
     */
    private function authorizeTeamMemberAccess(Employee $employee): void
    {
        $managerDeptId = Auth::user()->employee?->department_id;

        if (!$managerDeptId || $employee->department_id !== $managerDeptId) {
            abort(403, 'Você não tem permissão para visualizar este funcionário.');
        }
    }

    /**
     * API: Get dashboard stats.
     */
    public function stats(): \Illuminate\Http\JsonResponse
    {
        $department = Auth::user()->employee?->department;

        if (!$department) {
            return response()->json(['success' => false, 'message' => 'Departamento não encontrado'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->getDepartmentStats($department),
        ]);
    }

    /**
     * API: Get team members.
     */
    public function teamApi(): \Illuminate\Http\JsonResponse
    {
        $department = Auth::user()->employee?->department;

        if (!$department) {
            return response()->json(['success' => false, 'message' => 'Departamento não encontrado'], 404);
        }

        $members = Employee::with('user')
            ->where('department_id', $department->id)
            ->where('status', 'active')
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->user->name,
                'email' => $e->user->email,
                'position' => $e->position,
                'hire_date' => $e->hire_date->format('d/m/Y'),
                'status' => $e->status,
            ]);

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    /**
     * Clear dashboard cache.
     */
    public function clearCache(): \Illuminate\Http\RedirectResponse
    {
        $manager = Auth::user();
        $department = $manager->employee?->department;

        if ($department) {
            Cache::forget("gerente_dashboard_{$manager->id}_{$department->id}");
        }

        return back()->with('success', 'Cache do dashboard limpo!');
    }
}
