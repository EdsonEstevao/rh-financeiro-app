<?php
// app/Actions/ProcessPayroll.php

namespace App\Actions;

use Illuminate\Support\Facades\DB;

use App\Models\{Employee, Payroll};

class ProcessPayroll
{
    public function execute(int $departmentId): array
    {
        return DB::transaction(function () use ($departmentId) {
            $employees = Employee::where('department_id', $departmentId)
                ->where('status', 'active')
                ->get();

            $processed = [];

            foreach ($employees as $employee) {
                $payroll = Payroll::create([
                    'employee_id' => $employee->id,
                    'base_salary' => $employee->salary,
                    'deductions' => $this->calculateDeductions($employee),
                    'bonuses' => $this->calculateBonuses($employee),
                    'net_salary' => $this->calculateNetSalary($employee),
                    'period' => now()->format('Y-m'),
                ]);

                $processed[] = $payroll;
            }

            return $processed;
        });
    }

    private function calculateDeductions(Employee $employee): float
    {
        // INSS, IRRF, etc
        return 0.0;
    }

    private function calculateBonuses(Employee $employee): float
    {
        // Bônus, comissões, etc
        return 0.0;
    }

    private function calculateNetSalary(Employee $employee): float
    {
        return $employee->salary - $this->calculateDeductions($employee) + $this->calculateBonuses($employee);
    }
}
