<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\{Gate, URL};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    // public function boot(): void
    // {
    //     //
    // }
     public function boot(): void
    {
        // Usar Bootstrap para paginação
        Paginator::useTailwind();

        // Forçar HTTPS em produção
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // Gates personalizadas para autorização granular
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        // Gate para visualizar funcionários do próprio departamento (gerente)
        Gate::define('view-department-employees', function ($user, $departmentId) {
            if ($user->hasRole('admin|rh')) return true;
            if ($user->hasRole('gerente')) {
                return $user->employee && $user->employee->department_id === $departmentId;
            }
            return false;
        });

        // Gate para gerenciar folha de pagamento
        Gate::define('manage-payroll', function ($user) {
            return $user->hasRole('admin|rh');
        });

        // Gate para processar pagamentos
        Gate::define('process-payments', function ($user) {
            return $user->hasRole('admin|financeiro');
        });

        // Gate para acessar relatórios financeiros
        Gate::define('view-financial-reports', function ($user) {
            return $user->hasRole('admin|financeiro|gerente');
        });

        // Gate para acessar relatórios de RH
        Gate::define('view-rh-reports', function ($user) {
            return $user->hasRole('admin|rh|gerente');
        });

        // Gate para exportar relatórios
        Gate::define('export-reports', function ($user) {
            return $user->hasRole('admin|rh|financeiro');
        });
    }
}
