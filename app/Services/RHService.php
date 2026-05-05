<?php
// app/Services/RHService.php

namespace App\Services;

use Illuminate\Support\Facades\{Cache, DB};

use App\Actions\ProcessPayroll;
use App\Models\Employee;

class RHService
{
    public function __construct(
        private readonly ProcessPayroll $processPayroll,
        private readonly AuditService $auditService
    ) {}

    public function createEmployee(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $employee = Employee::create($data);

            // $this->auditService->log('employee_created', $employee->toArray());
            // ✅ CORRETO - passando array com dados do Employee
            // $this->auditService->log('Funcionário criado', [
            //     'employee_id' => $employee->id,
            //     'name' => $employee->name,
            //     // outros campos relevantes...
            // ]);
            $this->auditService->log('Funcionário criado');

            // Cache::tags(['employees'])->flush();
            Cache::increment('employees:version');

            return $employee;
        });
    }

    protected function getEmployeesCacheVersion(): int
    {
        return Cache::get('employees:version', 1);
    }

    protected function getEmployeesCacheKey(string $suffix): string
    {
        $version = $this->getEmployeesCacheVersion();
        return "employees:{$version}:{$suffix}";
    }

    public function updateEmployee(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $employee->fill($data)->save();

            $this->auditService->log('employee_updated', $employee->toArray());
            // Cache::tags(['employees'])->flush();
             Cache::increment('employees:version');

            return $employee->refresh();
        });
    }

    public function updateEmployeeAdvanced(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $employee->fill($data)->save();

            $this->auditService->log('employee_advanced_updated', $employee->toArray());
            // Cache::tags(['employees'])->flush();
             Cache::increment('employees:version');

            return $employee->refresh();
        });
    }

    public function listaFuncionarios()
    {
        $key = $this->getEmployeesCacheKey('list_all');

        return Cache::remember($key, 600, function () {
            return Employee::all();
        });
    }


    public function processMonthlyPayroll(int $departmentId): array
    {
        return $this->processPayroll->execute($departmentId);
    }
}
