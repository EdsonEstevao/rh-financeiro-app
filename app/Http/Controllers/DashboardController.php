<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

use App\Models\{Boleto, CreditCardTransaction, Employee};

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // $request->user()->authorizeRoles(['admin', 'rh', 'financeiro', 'consultor', 'gerente', 'funcionario']);
        $user = $request->user();



        return match($user->profile) {
            'admin' => $this->adminDashboard(),
            'rh' => $this->rhDashboard(),
            'financeiro' => $this->financeiroDashboard(),
            'consultor' => $this->consultorDashboard(),
            'gerente' => $this->gerenteDashboard(),
            'funcionario' => $this->funcionarioDashboard($user),
            default => view('dashboard'),
        };
    }

    private function adminDashboard(): View
    {
        $stats = [
            'total_users' => \App\Models\User::count(),
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

    private function rhDashboard(): View
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

    private function financeiroDashboard(): View
    {
        $stats = [
            'pending_boletos' => Boleto::where('status', 'pending')->count(),
            'overdue_boletos' => Boleto::where('status', 'overdue')->count(),
            'paid_today' => Boleto::where('status', 'paid')
                ->whereDate('paid_at', today())
                ->sum('amount'),
            'monthly_revenue' => Boleto::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->sum('amount'),
            'credit_card_transactions' => CreditCardTransaction::whereDate('created_at', today())
                ->count(),
            'recent_boletos' => Boleto::with('user')
                ->latest()
                ->take(10)
                ->get(),
        ];

        return view('financeiro.dashboard', compact('stats'));
    }

    private function consultorDashboard(): View
    {
        return view('consultor.dashboard', [
            'clients' => Employee::where('status', 'active')
                ->with('user')
                ->paginate(10)
        ]);
    }

    private function gerenteDashboard(): View
    {
        return view('gerente.dashboard', [
            'team' => Employee::where('department_id', Auth::user()->employee?->department_id)
                ->with('user')
                ->get(),
            'department_stats' => [
                'total' => Employee::where('department_id', Auth::user()->employee?->department_id)->count(),
                'active' => Employee::where('department_id', Auth::user()->employee?->department_id)
                    ->where('status', 'active')->count(),
            ]
        ]);
    }

    private function funcionarioDashboard($user): View
    {
        return view('funcionario.dashboard', [
            'employee' => $user->employee,
            'boletos' => $user->boletos()->latest()->take(5)->get(),
            'payroll_history' => \App\Models\Payroll::where('employee_id', $user->employee?->id)
                ->latest()
                ->take(6)
                ->get(),
        ]);
    }
}
