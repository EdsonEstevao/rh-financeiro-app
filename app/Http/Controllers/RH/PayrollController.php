<?php
// app/Http/Controllers/RH/PayrollController.php

namespace App\Http\Controllers\RH;

use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\View\View;
use Illuminate\Support\Facades\{Auth, DB, Log};
use Carbon\Carbon;

use App\Http\Controllers\Controller;
use App\Models\{Department, Employee, Payroll};
use App\Actions\ProcessPayroll;
use Barryvdh\DomPDF\Facade\PDF;

class PayrollController extends Controller
{
    public function __construct(
        private readonly ProcessPayroll $processPayroll
    ) {
        $this->middleware(['auth', 'role:admin|rh']);
        $this->middleware('permission:rh.payroll.process')->only(['processMonthly']);
    }

    /**
     * Display a listing of payrolls.
     */
    public function index(Request $request): View
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $query = Payroll::with(['employee.user', 'employee.department', 'processedBy'])
            ->where('year', $year)
            ->where('month', $month);

        // Aplicar filtros
        $query->when($request->filled('department'), function ($q) use ($request) {
            $q->whereHas('employee', function ($eq) use ($request) {
                $eq->where('department_id', $request->department);
            });
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        $query->when($request->filled('type'), function ($q) use ($request) {
            $q->where('type', $request->type);
        });

        $payrolls = $query->orderBy('employee_name')->get();

        // Resumo
        $summary = [
            'total_base_salary' => $payrolls->sum('base_salary'),
            'total_earnings' => $payrolls->sum('total_earnings'),
            'total_deductions' => $payrolls->sum('total_deductions'),
            'total_bonuses' => $payrolls->sum('bonus_amount'),
            'total_net_salary' => $payrolls->sum('net_salary'),
            'total_cost' => $payrolls->sum('total_cost'),
            'total_employees' => $payrolls->count(),
            'paid_count' => $payrolls->where('status', 'paid')->count(),
        ];

        $departments = Department::orderBy('name')->get();

        return view('rh.payroll.index', compact(
            'payrolls',
            'summary',
            'month',
            'year',
            'departments'
        ));
    }

    /**
     * Display the specified payroll.
     */
    public function show(Payroll $payroll): View
    {
        $payroll->load(['employee.user', 'employee.department', 'processedBy']);

        return view('rh.payroll.show', compact('payroll'));
    }

    /**
     * Process monthly payroll.
     */
    public function processMonthly(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'department_id' => ['nullable', 'exists:departments,id'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
        ]);

        try {
            DB::beginTransaction();

            $processedBatch = $this->processPayroll->execute(
                departmentId: $validated['department_id'],
                month: $validated['month'],
                year: $validated['year']
            );

            DB::commit();

            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                    'processed_count' => $processedBatch['count'] ?? 0,
                    'total_payroll' => $processedBatch['total'] ?? 0,
                ])
                ->log('Folha de pagamento processada');

            $message = sprintf(
                'Folha processada com sucesso! %d funcionários processados. Total: R$ %s',
                $processedBatch['count'] ?? 0,
                number_format($processedBatch['total'] ?? 0, 2, ',', '.')
            );

            return redirect()
                ->route('rh.payroll.index', [
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                ])
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to process payroll', [
                'error' => $e->getMessage(),
                'month' => $validated['month'],
                'year' => $validated['year'],
                'user_id' => Auth::id()
            ]);

            return back()->with('error', 'Erro ao processar folha: ' . $e->getMessage());
        }
    }

    /**
     * Generate payslip PDF.
     */
    public function generatePayslip(Payroll $payroll)
    {
        try {
            $payroll->load(['employee.user', 'employee.department']);

            $pdf = PDF::loadView('rh.payroll.payslip', [
                'payroll' => $payroll,
                'company' => [
                    'name' => config('app.name'),
                    'cnpj' => config('app.company_cnpj', '00.000.000/0000-00'),
                ]
            ]);

            $pdf->setPaper('a4');

            $filename = "holerite-{$payroll->employee_name}-{$payroll->year}-{$payroll->month}.pdf";

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Failed to generate payslip', [
                'error' => $e->getMessage(),
                'payroll_id' => $payroll->id
            ]);

            return back()->with('error', 'Erro ao gerar holerite.');
        }
    }

    /**
     * Mark payroll as paid.
     */
    public function markAsPaid(Payroll $payroll): RedirectResponse
    {
        try {
            $payroll->update([
                'status' => 'paid',
                'payment_date' => now(),
            ]);

            activity()
                ->performedOn($payroll)
                ->causedBy(Auth::user())
                ->log('Pagamento confirmado');

            return back()->with('success', 'Pagamento confirmado com sucesso!');

        } catch (\Exception $e) {
            Log::error('Failed to mark payroll as paid', [
                'error' => $e->getMessage(),
                'payroll_id' => $payroll->id
            ]);

            return back()->with('error', 'Erro ao confirmar pagamento.');
        }
    }

    /**
     * Export payroll to PDF report.
     */
    public function exportPDF(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $payrolls = Payroll::with('employee.user')
            ->where('year', $year)
            ->where('month', $month)
            ->get();

        $pdf = PDF::loadView('rh.payroll.report', [
            'payrolls' => $payrolls,
            'month' => $month,
            'year' => $year,
            'period' => Carbon::createFromDate($year, $month, 1)->format('m/Y'),
            'generated_at' => now()->format('d/m/Y H:i'),
            'summary' => [
                'total_net' => $payrolls->sum('net_salary'),
                'total_gross' => $payrolls->sum('gross_salary'),
                'count' => $payrolls->count(),
            ]
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download("folha-pagamento-{$year}-{$month}.pdf");
    }
}
