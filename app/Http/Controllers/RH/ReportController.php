<?php
// app/Http/Controllers/RH/ReportController.php

namespace App\Http\Controllers\RH;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\{Department, Employee, Payroll};
use App\Services\ReportService;


class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {
        $this->middleware(['auth', 'role:admin|rh|gerente']);
    }

    public function index(): View
    {
        $reportTypes = [
            [
                'id' => 'employees',
                'name' => 'Lista de Funcionários',
                'icon' => 'users',
                'description' => 'Relatório completo de todos os funcionários',
                'filters' => ['department', 'status']
            ],
            [
                'id' => 'payroll',
                'name' => 'Folha de Pagamento',
                'icon' => 'currency-dollar',
                'description' => 'Relatório detalhado da folha de pagamento',
                'filters' => ['month', 'year', 'department']
            ],
            [
                'id' => 'attendance',
                'name' => 'Controle de Ponto',
                'icon' => 'clock',
                'description' => 'Relatório de frequência e horas trabalhadas',
                'filters' => ['month', 'year', 'employee']
            ],
            [
                'id' => 'benefits',
                'name' => 'Benefícios',
                'icon' => 'gift',
                'description' => 'Relatório de benefícios concedidos',
                'filters' => ['month', 'year', 'type']
            ],
            [
                'id' => 'terminations',
                'name' => 'Desligamentos',
                'icon' => 'user-minus',
                'description' => 'Relatório de funcionários desligados',
                'filters' => ['period', 'reason']
            ]
        ];

        return view('rh.reports.index', compact('reportTypes'));
    }

    // Relatório de Funcionários
    public function employees(Request $request): View
    {
        $employees = Employee::with(['user', 'department'])
            ->when($request->department, function ($query, $department) {
                $query->where('department_id', $department);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('hire_date', 'desc')
            ->get();

        $departments = Department::orderBy('name', 'asc')->get();
        $totalSalary = $employees->sum('salary');

        return view('rh.reports.employees', compact('employees', 'departments', 'totalSalary'));
    }

    public function downloadEmployeesPDF(Request $request)
    {
        $data = $this->getEmployeeReportData($request);

        return $this->reportService->downloadPDF(
            'rh.reports.pdfs.employees',
            $data,
            'relatorio-funcionarios-' . now()->format('d-m-Y') . '.pdf',
            'landscape'
        );
    }

    public function streamEmployeesPDF(Request $request)
    {
        $data = $this->getEmployeeReportData($request);

        return $this->reportService->streamPDF(
            'rh.reports.pdfs.employees',
            $data,
            'relatorio-funcionarios-' . now()->format('d-m-Y') . '.pdf',
            'landscape'
        );
    }

    private function getEmployeeReportData(Request $request): array
    {
        $employees = Employee::with(['user', 'department'])
            ->when($request->department, function ($query, $department) {
                $query->where('department_id', $department);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('hire_date', 'desc')
            ->get();

        return [
            'employees' => $employees,
            'filters' => [
                'department' => $request->department ? Department::query()->find($request->department)->name : 'Todos',
                'status' => $request->status ?: 'Todos',
                'date' => now()->format('d/m/Y H:i')
            ],
            'stats' => [
                'total_employees' => $employees->count(),
                'total_salary' => $employees->sum('salary'),
                'active_employees' => $employees->where('status', 'active')->count(),
            ]
        ];
    }

    // Relatório de Folha de Pagamento
    public function payroll(Request $request): View
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $payrolls = Payroll::with(['employee.user', 'employee.department'])
            ->whereMonth('period', $month)
            ->whereYear('period', $year)
            ->when($request->department, function ($query, $department) {
                $query->whereHas('employee', function ($q) use ($department) {
                    $q->where('department_id', $department);
                });
            })
            ->get();

        $departments = Department::orderBy('name', 'asc')->get();

        $summary = [
            'total_base_salary' => $payrolls->sum('base_salary'),
            'total_deductions' => $payrolls->sum('deductions'),
            'total_bonuses' => $payrolls->sum('bonuses'),
            'total_net_salary' => $payrolls->sum('net_salary'),
            'total_employees' => $payrolls->count(),
        ];

        return view('rh.reports.payroll', compact('payrolls', 'departments', 'summary', 'month', 'year'));
    }

    public function downloadPayrollPDF(Request $request)
    {
        $data = $this->getPayrollReportData($request);

        return $this->reportService->downloadPDF(
            'rh.reports.pdfs.payroll',
            $data,
            'folha-pagamento-' . $request->month . '-' . $request->year . '.pdf',
            'landscape'
        );
    }

    private function getPayrollReportData(Request $request): array
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $payrolls = Payroll::with(['employee.user', 'employee.department'])
            ->whereMonth('period', $month)
            ->whereYear('period', $year)
            ->when($request->department, function ($query, $department) {
                $query->whereHas('employee', function ($q) use ($department) {
                    $q->where('department_id', $department);
                });
            })
            ->get();

        return [
            'payrolls' => $payrolls,
            'month' => $month,
            'year' => $year,
            'filters' => [
                'department' => $request->department ? Department::query()->find($request->department)->name : 'Todos',
                'period' => Carbon::createFromDate($year, $month, 1)->format('m/Y')
            ],
            'summary' => [
                'total_base_salary' => $payrolls->sum('base_salary'),
                'total_deductions' => $payrolls->sum('deductions'),
                'total_bonuses' => $payrolls->sum('bonuses'),
                'total_net_salary' => $payrolls->sum('net_salary'),
            ]
        ];
    }
}
