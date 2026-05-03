<?php
// app/Http/Controllers/RH/EmployeeController.php

namespace App\Http\Controllers\RH;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeRequest;
use App\Models\Employee;
use App\Services\RHService;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly RHService $rhService
    ) {
        $this->middleware('check.profile:rh,admin');
    }

    public function index(): View
    {
        $employees = Employee::with(['department', 'user'])
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(15);

        return view('rh.employees.index', compact('employees'));
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        try {
            $employee = $this->rhService->createEmployee($request->validated());

            return redirect()
                ->route('rh.employees.show', $employee)
                ->with('success', 'Funcionário cadastrado com sucesso!');

        } catch (\Exception $e) {
            report($e);
            return back()
                ->withInput()
                ->with('error', 'Erro ao cadastrar funcionário.');
        }
    }
}
