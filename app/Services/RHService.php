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

            // Registrar auditoria
            $this->auditService->log('employee_created', $employee);

            // Limpar cache relacionado
            Cache::tags(['employees'])->flush();

            return $employee;
        });
    }

    public function processMonthlyPayroll(int $departmentId): array
    {
        return $this->processPayroll->execute($departmentId);
    }
}
