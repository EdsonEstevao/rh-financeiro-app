<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

use App\Models\{Boleto, CreditCardTransaction, Employee, User};

class DashboardController extends Controller
{
    /**
     * Display the appropriate dashboard based on user role.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Redirecionar para o dashboard correto baseado no perfil
        if ($user->hasRole('admin')) {
            return $this->admin();
        }

        if ($user->hasRole('rh')) {
            return $this->rh();
        }

        if ($user->hasRole('financeiro')) {
            return $this->financeiro();
        }

        if ($user->hasRole('consultor')) {
            return $this->consultor();
        }

        if ($user->hasRole('gerente')) {
            return $this->gerente();
        }

        if ($user->hasRole('funcionario')) {
            return $this->funcionario();
        }

        // Dashboard padrão para usuários sem perfil específico
        return view('dashboard');
    }

    /**
     * Admin dashboard.
     */
    public function admin(): View
    {
        $stats = [
            'total_users' => User::count(),
            'total_employees' => Employee::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'pending_boletos' => Boleto::where('status', 'pending')->count(),
            'total_revenue' => Boleto::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->sum('amount'),
            'recent_transactions' => CreditCardTransaction::with('user')
                ->latest()
                ->take(10)
                ->get(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * RH dashboard.
     */
    public function rh(): View
    {
        $stats = [
            'total_employees' => Employee::count(),
            'active_employees' => Employee::where('status', 'active')->count(),
            'on_vacation' => Employee::where('status', 'vacation')->count(),
            'new_hires_this_month' => Employee::whereMonth('hire_date', now()->month)
                ->whereYear('hire_date', now()->year)
                ->count(),
            'departments' => \App\Models\Department::withCount('employees')->get(),
            'recent_hires' => Employee::with('user')
                ->latest('hire_date')
                ->take(5)
                ->get(),
        ];

        return view('rh.dashboard', compact('stats'));
    }

    /**
     * Financeiro dashboard.
     */
    public function financeiro(): View
    {
        $stats = [
            'pending_boletos' => Boleto::where('status', 'pending')->count(),
            'overdue_boletos' => Boleto::where('status', 'overdue')
                ->orWhere(function ($q) {
                    $q->where('status', 'pending')->where('due_date', '<', now());
                })->count(),
            'paid_today' => Boleto::where('status', 'paid')
                ->whereDate('paid_at', today())
                ->sum('amount'),
            'monthly_revenue' => Boleto::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->sum('amount'),
            'credit_card_transactions' => CreditCardTransaction::whereDate('created_at', today())->count(),
            'recent_boletos' => Boleto::with('user')
                ->latest()
                ->take(10)
                ->get(),
        ];

        return view('financeiro.dashboard', compact('stats'));
    }

    /**
     * Consultor dashboard.
     */
    public function consultor(): View
    {
        return view('consultor.dashboard', [
            'clients' => User::role('funcionario')
                ->where('is_active', true)
                ->with('employee.department')
                ->paginate(10)
        ]);
    }

    /**
     * Gerente dashboard.
     */
    public function gerente(): View
    {
        $departmentId = Auth::user()->employee?->department_id;

        return view('gerente.dashboard', [
            'team' => $departmentId
                ? Employee::where('department_id', $departmentId)->with('user')->get()
                : collect(),
            'department_stats' => $departmentId ? [
                'total' => Employee::where('department_id', $departmentId)->count(),
                'active' => Employee::where('department_id', $departmentId)
                    ->where('status', 'active')->count(),
            ] : ['total' => 0, 'active' => 0],
        ]);
    }

    /**
     * Funcionário dashboard.
     */
    public function funcionario(): View
    {
        $user = Auth::user();
        $employee = $user->employee;

        return view('funcionario.dashboard', [
            'employee' => $employee,
            'boletos' => $user->boletos()->latest()->take(5)->get(),
            'payroll_history' => $employee
                ? \App\Models\Payroll::where('employee_id', $employee->id)
                    ->latest()
                    ->take(6)
                    ->get()
                : collect(),
        ]);
    }
}
