<?php
// app/Http/Controllers/RH/EmployeeController.php

namespace App\Http\Controllers\RH;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{Auth, DB, Log};
use Illuminate\Database\Eloquent\Builder;

use App\Http\Controllers\Controller;
use App\Models\{Department, Employee};
use App\Http\Requests\EmployeeRequest;
use App\Services\RHService;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly RHService $rhService
    ) {
        $this->middleware(['auth', 'role:admin|rh|gerente']);

        // Permissões granulares
        $this->middleware('permission:rh.employees.create')->only(['create', 'store']);
        $this->middleware('permission:rh.employees.edit')->only(['edit', 'update']);
        $this->middleware('permission:rh.employees.delete')->only(['destroy']);

        // Gerente só pode ver funcionários do seu departamento
        $this->middleware(function ($request, $next) {


            if (Auth::user()->hasRole('gerente')) {
                // $employeeId = $request->route('employees')?->id;
                $employeeId = (int) $request->input('id') ?? (int)  $request->input('employee_id');

                if ($employeeId) {
                    $employee = Employee::find($employeeId, ['id', 'department_id']);
                    if ($employee && $employee->department_id !== Auth::user()->employee?->department_id) {
                        abort(403, 'Você não tem permissão para acessar este funcionário.');
                    }
                }
            }
            return $next($request);
        })->only(['show', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of employees.
     */
    public function index(Request $request): View
    {
        $query = Employee::with(['user', 'department', 'supervisor']);

        // Gerente vê apenas seu departamento
        if (Auth::user()->hasRole('gerente')) {
            $query->where('department_id', Auth::user()->employee?->department_id);
        }

        // Aplicar filtros
        $this->applyFilters($query, $request);

        // Estatísticas
        $stats = $this->getStats($query);

        $employees = $query->latest('hire_date')->paginate(15)->withQueryString();

        // Dados para filtros
        $departments = Department::orderBy('name', 'asc')->get();

        return view('rh.employees.index', compact('employees', 'stats', 'departments'));
    }

    /**
     * Show the form for creating a new employee.
     */
     public function create()
    {
        return view('rh.employees.create', [
            'departments' => Department::query()->orderBy('name', 'asc')->get(),
            'supervisors' => Employee::query()->with('user:id,name,email')->orderByDesc('id')->limit(200)->get(),
        ]);
    }

    /**
     * Store a newly created employee.
     */
    // public function store(Request $request): RedirectResponse
    public function store(EmployeeRequest $request)
    {
        // dd($request->validated());
        $employee = $this->rhService->createEmployee($request->validated());

        return redirect()
            ->route('rh.employees.advanced.edit', $employee)
            ->with('success', 'Funcionário criado. Complete os detalhes avançados.');
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): View
    {
        $employee->load([
            'user',
            'department',
            'supervisor.user',
            'subordinates.user',
            'documents' => fn($q) => $q->latest()->limit(10),
            'payrolls' => fn($q) => $q->latest()->limit(12),
        ]);

        // Estatísticas do funcionário
        $employeeStats = [
            'total_payrolls' => $employee->payrolls()->count(),
            'total_earned' => $employee->payrolls()->sum('net_salary'),
            'last_salary' => $employee->payrolls()->latest()->first()?->net_salary,
            'documents_count' => $employee->documents()->count(),
            'pending_documents' => $employee->documents()->where('status', 'pending')->count(),
            'expiring_documents' => $employee->documents()
                ->where('expiration_date', '<=', now()->addDays(30))
                ->where('expiration_date', '>=', now())
                ->count(),
            'vacation_days_remaining' => $employee->vacation_days_available,
            'years_of_service' => $employee->years_of_service,
        ];

        return view('rh.employees.show', compact('employee', 'employeeStats'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee): View
    {
        $employee->load('user', 'department', 'supervisor');

        $departments = Department::query()->where('is_active', true)->orderBy('name', 'asc')->get();
        $supervisors = Employee::with('user')
            ->where('status', 'active')
            ->where('id', '!=', $employee->id)
            ->get();

        return view('rh.employees.edit', compact('employee', 'departments', 'supervisors'));
    }

    /**
     * Update the specified employee.
     */
    public function update(EmployeeRequest $request, Employee $employee): RedirectResponse
    {
         $this->rhService->updateEmployee($employee, $request->validated());

        return back()->with('success', 'Dados básicos atualizados.');
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $userName = $employee->user?->name ?? 'N/A';

            // Soft delete do funcionário
            Employee::destroy($employee->id);

            // Desativar usuário (se existir)
            $employee->user()->update(['is_active' => false]);

            DB::commit();

            activity()
                ->performedOn($employee)
                ->causedBy(Auth::user())
                ->log('Funcionário desligado');

            return redirect()
                ->route('rh.employees.index')
                ->with('success', "Funcionário {$userName} desligado com sucesso!");

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Failed to delete employee', [
                'error' => $e->getMessage(),
                'employee_id' => $employee->id
            ]);

            return back()->with('error', 'Erro ao desligar funcionário.');
        }
    }


    /**
     * Restore a soft-deleted employee.
     */
    public function restore(int $id): RedirectResponse
    {
        try {
            $employee = Employee::withTrashed()->findOrFail($id);

            DB::beginTransaction();

            $employee->restore();

            // Reativar usuário (se existir) - CORRIGIDO
            $employee->user()->update(['is_active' => true]);

            DB::commit();

            activity()
                ->performedOn($employee)
                ->causedBy(Auth::user())
                ->log('Funcionário restaurado');

            return back()->with('success', 'Funcionário restaurado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to restore employee', [
                'error' => $e->getMessage(),
                'employee_id' => $id
            ]);

            return back()->with('error', 'Erro ao restaurar funcionário.');
        }
    }

    /**
     * Export employees to PDF.
     */
    // public function exportPDF(Request $request)
    // {
    //     $query = Employee::with(['user', 'department']);

    //     if (Auth::user()->hasRole('gerente')) {
    //         $query->where('department_id', Auth::user()->employee?->department_id);
    //     }

    //     $this->applyFilters($query, $request);
    //     $employees = $query->get();

    //     $pdf = PDF::loadView('rh.reports.pdfs.employees', [
    //         'employees' => $employees,
    //         'generated_at' => now()->format('d/m/Y H:i')
    //     ]);

    //     $pdf->setPaper('a4', 'landscape');

    //     return $pdf->download('funcionarios-' . now()->format('d-m-Y') . '.pdf');
    // }
    public function exportPDF(Request $request)
    {
        $query = Employee::query()->with(['user', 'department']);

        if (Auth::user()->hasRole('gerente')) {
            $query->where('department_id', Auth::user()->employee?->department_id);
        }

        $this->applyFilters($query, $request);

        $employees = $query->get();

        $filters = [
            'department' => $request->filled('department') ? (string) $request->department : 'Todos',
            'status'     => $request->filled('status') ? (string) $request->status : 'Todos',
            'date'       => now()->format('d/m/Y H:i'),
        ];

        $stats = [
            'total_employees'  => $employees->count(),
            'active_employees' => $employees->where('status', 'active')->count(),
            'total_salary'     => (float) $employees->sum('salary'),
        ];

        $pdf = Pdf::loadView('rh.reports.pdfs.employees', [
            'employees'    => $employees,
            'filters'      => $filters,
            'stats'        => $stats,
            'generated_at' => $filters['date'],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('funcionarios-' . now()->format('d-m-Y') . '.pdf');
    }

    /**
     * API: Search employees.
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $search = $request->input('q');

        $employees = Employee::with('user')
            ->where(function ($q) use ($search) {
                $q->whereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                       ->orWhere('cpf', 'like', "%{$search}%");
                })->orWhere('registration_number', 'like', "%{$search}%");
            })
            ->where('status', 'active')
            ->limit(10)
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->user->name,
                'cpf' => $e->user->cpf,
                'position' => $e->position,
                'department' => $e->department?->name,
            ]);

        return response()->json($employees);
    }

    /**
     * Apply filters to query.
     */
    private function applyFilters(Builder $query, Request $request): void
    {
        $query->when($request->filled('search'), function ($q) use ($request) {
            $q->where(function ($sq) use ($request) {
                $sq->whereHas('user', function ($uq) use ($request) {
                    $uq->where('name', 'like', "%{$request->search}%")
                       ->orWhere('email', 'like', "%{$request->search}%")
                       ->orWhere('cpf', 'like', "%{$request->search}%");
                })
                ->orWhere('position', 'like', "%{$request->search}%")
                ->orWhere('registration_number', 'like', "%{$request->search}%");
            });
        });

        $query->when($request->filled('department'), function ($q) use ($request) {
            $q->where('department_id', $request->department);
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        $query->when($request->filled('employment_type'), function ($q) use ($request) {
            $q->where('employment_type', $request->employment_type);
        });
    }

    /**
     * Get employee statistics.
     */
    private function getStats($query): array
    {
        $baseQuery = Employee::query();

        if (Auth::user()->hasRole('gerente')) {
            $baseQuery->where('department_id', Auth::user()->employee?->department_id);
        }

        return [
            'total' => $baseQuery->count('id'),
            'active' => $baseQuery->where('status', 'active')->count('id'),
            'on_vacation' => $baseQuery->where('status', 'vacation')->count('id'),
            'on_leave' => $baseQuery->where('status', 'leave')->count('id'),
            'terminated' => $baseQuery->where('status', 'terminated')->count('id'),
            'total_payroll' => $baseQuery->where('status', 'active')->sum('salary'),
            'new_this_month' => $baseQuery->whereMonth('hire_date', now()->month, true, '>=')->count('id'),
            'avg_salary' => $baseQuery->where('status', 'active')->avg('salary') ?? 0,
        ];
    }

    /**
     * Generate a random password for new employees.
     */
    private function generatePassword(): string
    {
        return substr(str_shuffle('abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$%'), 0, 12);
    }
}
