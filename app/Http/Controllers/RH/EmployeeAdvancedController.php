<?php
// app/Http/Controllers/Rh/EmployeeAdvancedController.php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeAdvancedRequest;
use App\Models\Employee;
use App\Services\RHService;

class EmployeeAdvancedController extends Controller
{
    public function __construct(private readonly RHService $rh) {}

    public function edit(Employee $employee)
    {
        return view('rh.employees.advanced.edit', [
            'employee' => $employee,
        ]);
    }

    public function update(EmployeeAdvancedRequest $request, Employee $employee)
    {
        $this->rh->updateEmployeeAdvanced($employee, $request->validated());

        return back()->with('success', 'Detalhes avançados atualizados.');
    }
}