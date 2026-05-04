<?php
// app/Http/Controllers/Funcionario/PayrollController.php

namespace App\Http\Controllers\Funcionario;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\{Employee, Payroll};
use Barryvdh\DomPDF\Facade\Pdf;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|funcionario']);
        $this->middleware('permission:funcionario.payroll.view');
    }

    /**
     * Display a listing of the employee's payrolls.
     */
    public function index(Request $request): View
    {
        $employee = $this->getEmployee();

        if (!$employee) {
            return view('funcionario.payroll.index', [
                'payrolls' => collect(),
                'stats' => $this->getEmptyStats(),
            ])->with('warning', 'Você não possui vínculo empregatício ativo.');
        }

        $query = Payroll::where('employee_id', $employee->id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc');

        // Aplicar filtros
        $this->applyFilters($query, $request);

        // Estatísticas
        $stats = $this->getPersonalPayrollStats($employee);

        $payrolls = $query->paginate(12)->withQueryString();

        return view('funcionario.payroll.index', compact('payrolls', 'stats', 'employee'));
    }

    /**
     * Display the specified payroll.
     */
    public function show(Payroll $payroll): View
    {
        // Verificar se a folha pertence ao funcionário logado
        $this->authorizePayrollAccess($payroll);

        $payroll->load(['employee.user', 'employee.department', 'processedBy']);

        // Calcular totais para exibição
        $totals = [
            'earnings' => $payroll->total_earnings,
            'deductions' => $payroll->total_deductions,
            'net_salary' => $payroll->net_salary,
            'fgts' => $payroll->fgts_amount,
            'inss' => $payroll->inss_amount,
            'irrf' => $payroll->irrf_amount,
        ];

        // Dados de earnings (proventos) em array
        $earningsList = is_array($payroll->earnings)
            ? $payroll->earnings
            : json_decode($payroll->earnings, true) ?? [];

        // Dados de deductions (descontos) em array
        $deductionsList = is_array($payroll->deductions)
            ? $payroll->deductions
            : json_decode($payroll->deductions, true) ?? [];

        return view('funcionario.payroll.show', compact(
            'payroll',
            'totals',
            'earningsList',
            'deductionsList'
        ));
    }

    /**
     * Download payslip/holerite as PDF.
     */
    public function downloadPayslip(Payroll $payroll)
    {
        $this->authorizePayrollAccess($payroll);

        try {
            $payroll->load(['employee.user', 'employee.department']);

            $employee = $payroll->employee;
            $user = $employee->user;

            $pdf = PDF::loadView('funcionario.payroll.payslip', [
                'payroll' => $payroll,
                'employee' => $employee,
                'user' => $user,
                'company' => [
                    'name' => config('app.name'),
                    'cnpj' => config('app.company_cnpj', '00.000.000/0000-00'),
                    'address' => config('app.company_address', ''),
                    'logo' => public_path('images/logo.png'),
                ],
                'generated_at' => now()->format('d/m/Y H:i:s'),
            ]);

            $pdf->setPaper('a4');
            $pdf->setOptions([
                'defaultFont' => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

            $period = Carbon::createFromDate($payroll->year, $payroll->month, 1)
                ->translatedFormat('F_Y');

            $filename = sprintf(
                'holerite-%s-%s-%s.pdf',
                Str::slug($user->name),
                $payroll->year,
                str_pad($payroll->month, 2, '0', STR_PAD_LEFT)
            );

            // Log do download
            activity()
                ->performedOn($payroll)
                ->causedBy(auth()->user())
                ->withProperties([
                    'period' => $payroll->period,
                    'type' => $payroll->type,
                ])
                ->log('Holerite baixado pelo funcionário');

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Failed to generate payslip PDF', [
                'error' => $e->getMessage(),
                'payroll_id' => $payroll->id,
                'user_id' => auth()->id(),
            ]);

            return back()->with('error', 'Erro ao gerar holerite. Por favor, tente novamente.');
        }
    }

    /**
     * Stream payslip in browser.
     */
    public function streamPayslip(Payroll $payroll)
    {
        $this->authorizePayrollAccess($payroll);

        $payroll->load(['employee.user', 'employee.department']);

        $pdf = PDF::loadView('funcionario.payroll.payslip', [
            'payroll' => $payroll,
            'employee' => $payroll->employee,
            'user' => $payroll->employee->user,
            'company' => [
                'name' => config('app.name'),
                'cnpj' => config('app.company_cnpj', '00.000.000/0000-00'),
            ],
        ]);

        return $pdf->stream('holerite.pdf');
    }

    /**
     * Get payroll data as JSON.
     */
    public function getPayrollData(Payroll $payroll): \Illuminate\Http\JsonResponse
    {
        $this->authorizePayrollAccess($payroll);

        return response()->json([
            'id' => $payroll->id,
            'period' => Carbon::createFromDate($payroll->year, $payroll->month, 1)
                ->translatedFormat('F/Y'),
            'type' => $payroll->type,
            'type_label' => $this->getPayrollTypeLabel($payroll->type),
            'base_salary' => number_format($payroll->base_salary, 2, ',', '.'),
            'earnings' => $this->formatItems($payroll->earnings),
            'total_earnings' => number_format($payroll->total_earnings, 2, ',', '.'),
            'deductions' => $this->formatItems($payroll->deductions),
            'total_deductions' => number_format($payroll->total_deductions, 2, ',', '.'),
            'gross_salary' => number_format($payroll->gross_salary, 2, ',', '.'),
            'net_salary' => number_format($payroll->net_salary, 2, ',', '.'),
            'status' => $payroll->status,
            'payment_date' => $payroll->payment_date?->format('d/m/Y'),
            'reference_number' => $payroll->reference_number,
        ]);
    }

    /**
     * Get payroll summary for dashboard.
     */
    public function summary(): \Illuminate\Http\JsonResponse
    {
        $employee = $this->getEmployee();

        if (!$employee) {
            return response()->json($this->getEmptyStats());
        }

        return response()->json($this->getPersonalPayrollStats($employee));
    }

    /**
     * Get yearly summary.
     */
    public function yearlySummary(Request $request): \Illuminate\Http\JsonResponse
    {
        $employee = $this->getEmployee();

        if (!$employee) {
            return response()->json([]);
        }

        $year = $request->get('year', now()->year);

        $payrolls = Payroll::where('employee_id', $employee->id)
            ->where('year', $year)
            ->orderBy('month')
            ->get();

        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthPayroll = $payrolls->firstWhere('month', $month);

            $monthlyData[] = [
                'month' => $month,
                'month_name' => Carbon::createFromDate($year, $month, 1)->translatedFormat('M'),
                'net_salary' => $monthPayroll ? (float) $monthPayroll->net_salary : 0,
                'has_payroll' => !is_null($monthPayroll),
            ];
        }

        $totals = [
            'total_net' => $payrolls->sum('net_salary'),
            'total_gross' => $payrolls->sum('gross_salary'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_irrf' => $payrolls->sum('irrf_amount'),
            'total_inss' => $payrolls->sum('inss_amount'),
            'total_fgts' => $payrolls->sum('fgts_amount'),
            'months_worked' => $payrolls->count(),
            'average_salary' => $payrolls->count() > 0
                ? $payrolls->avg('net_salary')
                : 0,
        ];

        return response()->json([
            'year' => $year,
            'monthly_data' => $monthlyData,
            'totals' => $totals,
        ]);
    }

    /**
     * Apply filters to payroll query.
     */
    private function applyFilters($query, Request $request): void
    {
        $query->when($request->filled('year'), function ($q) use ($request) {
            $q->where('year', $request->year);
        });

        $query->when($request->filled('type'), function ($q) use ($request) {
            $q->where('type', $request->type);
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });
    }

    /**
     * Get employee associated with the logged-in user.
     */
    private function getEmployee(): ?Employee
    {
        return auth()->user()->employee()
            ->where('status', 'active')
            ->first();
    }

    /**
     * Get personalized payroll statistics.
     */
    private function getPersonalPayrollStats(Employee $employee): array
    {
        $payrolls = Payroll::where('employee_id', $employee->id);
        $currentYear = now()->year;

        $latestPayroll = (clone $payrolls)->latest()->first();
        $yearPayrolls = (clone $payrolls)->where('year', $currentYear);
        $last12Months = (clone $payrolls)->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->take(12);

        return [
            'total_payrolls' => $payrolls->count(),
            'latest_net_salary' => $latestPayroll?->net_salary ?? 0,
            'latest_month' => $latestPayroll
                ? Carbon::createFromDate($latestPayroll->year, $latestPayroll->month, 1)
                    ->translatedFormat('F/Y')
                : '---',
            'latest_type' => $latestPayroll?->type ?? '---',
            'latest_status' => $latestPayroll?->status ?? '---',

            'year_total_net' => $yearPayrolls->sum('net_salary'),
            'year_total_gross' => $yearPayrolls->sum('gross_salary'),
            'year_total_deductions' => $yearPayrolls->sum('total_deductions'),
            'year_total_irrf' => $yearPayrolls->sum('irrf_amount'),
            'year_total_inss' => $yearPayrolls->sum('inss_amount'),
            'year_total_fgts' => $yearPayrolls->sum('fgts_amount'),
            'year_months_worked' => $yearPayrolls->count(),

            'last_12_months_net' => $last12Months->sum('net_salary'),
            'last_12_months_count' => $last12Months->count(),
            'average_salary' => $payrolls->count() > 0
                ? $payrolls->avg('net_salary')
                : 0,
            'highest_salary' => $payrolls->max('net_salary') ?? 0,
            'lowest_salary' => $payrolls->min('net_salary') ?? 0,

            'has_thirteenth' => $yearPayrolls->where('type', 'thirteenth')->count() > 0,
            'thirteenth_amount' => $yearPayrolls->where('type', 'thirteenth')->sum('net_salary'),
            'has_vacation' => $yearPayrolls->where('type', 'vacation')->count() > 0,
            'vacation_amount' => $yearPayrolls->where('type', 'vacation')->sum('net_salary'),
        ];
    }

    /**
     * Get empty stats for users without employee record.
     */
    private function getEmptyStats(): array
    {
        return [
            'total_payrolls' => 0,
            'latest_net_salary' => 0,
            'latest_month' => '---',
            'latest_type' => '---',
            'latest_status' => '---',
            'year_total_net' => 0,
            'year_total_gross' => 0,
            'year_total_deductions' => 0,
            'year_total_irrf' => 0,
            'year_total_inss' => 0,
            'year_total_fgts' => 0,
            'year_months_worked' => 0,
            'last_12_months_net' => 0,
            'last_12_months_count' => 0,
            'average_salary' => 0,
            'highest_salary' => 0,
            'lowest_salary' => 0,
            'has_thirteenth' => false,
            'thirteenth_amount' => 0,
            'has_vacation' => false,
            'vacation_amount' => 0,
        ];
    }

    /**
     * Authorize payroll access.
     */
    private function authorizePayrollAccess(Payroll $payroll): void
    {
        $employee = $this->getEmployee();

        if (!$employee) {
            abort(403, 'Você não possui vínculo empregatício ativo.');
        }

        if ($payroll->employee_id !== $employee->id && !auth()->user()->hasRole('admin')) {
            abort(403, 'Você não tem permissão para acessar este holerite.');
        }
    }

    /**
     * Get payroll type label.
     */
    private function getPayrollTypeLabel(string $type): string
    {
        return match ($type) {
            'monthly' => 'Mensal',
            'thirteenth' => '13º Salário',
            'vacation' => 'Férias',
            'bonus' => 'Bônus',
            'advance' => 'Adiantamento',
            'termination' => 'Rescisão',
            'overtime' => 'Horas Extras',
            default => ucfirst($type),
        };
    }

    /**
     * Format earnings/deductions items.
     */
    private function formatItems($items): array
    {
        if (is_string($items)) {
            $items = json_decode($items, true);
        }

        if (!is_array($items)) {
            return [];
        }

        return array_map(function ($item) {
            if (is_array($item) && isset($item['amount'])) {
                $item['amount_formatted'] = number_format($item['amount'], 2, ',', '.');
            }
            return $item;
        }, $items);
    }
}