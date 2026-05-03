<?php
// routes/web.php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{DashboardController, ProfileController};
use App\Http\Controllers\Admin\{AuditController, SettingsController, UserController};
use App\Http\Controllers\RH\{DocumentController, EmployeeController, PayrollController as RHPayrollController, ReportController as RHReportController};
use App\Http\Controllers\Financeiro\{BoletoController, CreditCardController, ReportController as FinanceiroReportController};
use App\Http\Controllers\Funcionario\{BoletoController as FuncionarioBoletoController, PayrollController as FuncionarioPayrollController};
use App\Http\Controllers\Consultor\DashboardController as ConsultorDashboardController;
use App\Http\Controllers\Gerente\DashboardController as GerenteDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rota inicial - redireciona para login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rotas de autenticação (Breeze)
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Rotas Autenticadas e Verificadas
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard Dinâmico (por perfil)
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Rotas de Perfil (Breeze)
    |--------------------------------------------------------------------------
    */
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas do Administrador
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Dashboard Admin
            Route::get('/dashboard', function () {
                return app(DashboardController::class)->adminDashboard();
            })->name('dashboard');

            // Gerenciamento de Usuários
            Route::resource('users', UserController::class);
            Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');
            Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');

            // Auditoria
            Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
            Route::get('/audit/{audit}', [AuditController::class, 'show'])->name('audit.show');
            Route::get('/audit/export/pdf', [AuditController::class, 'exportPDF'])->name('audit.export.pdf');
            Route::get('/audit/export/excel', [AuditController::class, 'exportExcel'])->name('audit.export.excel');

            // Configurações
            Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
            Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
            Route::post('/settings/backup', [SettingsController::class, 'backup'])->name('settings.backup');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas do RH
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin|rh|gerente'])
        ->prefix('rh')
        ->name('rh.')
        ->group(function () {

            // Dashboard RH
            Route::get('/dashboard', function () {
                return app(DashboardController::class)->rhDashboard();
            })->name('dashboard');

            // Funcionários
            Route::resource('employees', EmployeeController::class);
            Route::post('employees/{employee}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
            Route::get('employees/export/pdf', [EmployeeController::class, 'exportPDF'])->name('employees.export.pdf');
            Route::get('employees/export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export.excel');

            // Rotas exclusivas do RH
            Route::middleware(['role:admin|rh'])->group(function () {
                // Folha de Pagamento
                Route::resource('payroll', RHPayrollController::class);
                Route::post('payroll/process', [RHPayrollController::class, 'processMonthly'])->name('payroll.process');
                Route::get('payroll/{payroll}/payslip', [RHPayrollController::class, 'generatePayslip'])->name('payroll.payslip');

                // Documentos
                Route::resource('documents', DocumentController::class);
                Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
                Route::post('documents/{document}/approve', [DocumentController::class, 'approve'])->name('documents.approve');
                Route::post('documents/{document}/reject', [DocumentController::class, 'reject'])->name('documents.reject');

                // Relatórios RH
                Route::prefix('reports')->name('reports.')->group(function () {
                    Route::get('/', [RHReportController::class, 'index'])->name('index');
                    Route::get('/employees', [RHReportController::class, 'employees'])->name('employees');
                    Route::get('/employees/pdf', [RHReportController::class, 'downloadEmployeesPDF'])->name('employees.pdf');
                    Route::get('/employees/stream', [RHReportController::class, 'streamEmployeesPDF'])->name('employees.stream');
                    Route::get('/payroll', [RHReportController::class, 'payroll'])->name('payroll');
                    Route::get('/payroll/pdf', [RHReportController::class, 'downloadPayrollPDF'])->name('payroll.pdf');
                    Route::get('/payroll/stream', [RHReportController::class, 'streamPayrollPDF'])->name('payroll.stream');
                    Route::get('/attendance', [RHReportController::class, 'attendance'])->name('attendance');
                    Route::get('/attendance/pdf', [RHReportController::class, 'downloadAttendancePDF'])->name('attendance.pdf');
                    Route::get('/benefits', [RHReportController::class, 'benefits'])->name('benefits');
                    Route::get('/benefits/pdf', [RHReportController::class, 'downloadBenefitsPDF'])->name('benefits.pdf');
                    Route::get('/terminations', [RHReportController::class, 'terminations'])->name('terminations');
                    Route::get('/terminations/pdf', [RHReportController::class, 'downloadTerminationsPDF'])->name('terminations.pdf');
                });
            });
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas do Financeiro
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin|financeiro|consultor'])
        ->prefix('financeiro')
        ->name('financeiro.')
        ->group(function () {

            // Dashboard Financeiro
            Route::get('/dashboard', function () {
                return app(DashboardController::class)->financeiroDashboard();
            })->name('dashboard');

            // Boletos
            Route::resource('boletos', BoletoController::class);
            Route::get('boletos/{boleto}/pdf', [BoletoController::class, 'downloadPDF'])->name('boletos.pdf');
            Route::get('boletos/{boleto}/stream', [BoletoController::class, 'streamPDF'])->name('boletos.stream');
            Route::post('boletos/{boleto}/mark-paid', [BoletoController::class, 'markAsPaid'])->name('boletos.mark-paid');
            Route::post('boletos/{boleto}/cancel', [BoletoController::class, 'cancel'])->name('boletos.cancel');
            Route::post('boletos/{boleto}/send-email', [BoletoController::class, 'sendByEmail'])->name('boletos.send-email');

            // Cartões de Crédito
            Route::resource('credit-cards', CreditCardController::class)->parameters([
                'credit-cards' => 'transaction'
            ]);
            Route::post('credit-cards/process', [CreditCardController::class, 'processPayment'])->name('credit-cards.process');
            Route::post('credit-cards/{transaction}/refund', [CreditCardController::class, 'refund'])->name('credit-cards.refund');
            Route::get('credit-cards/{transaction}/receipt', [CreditCardController::class, 'generateReceipt'])->name('credit-cards.receipt');

            // Rotas exclusivas do Financeiro
            Route::middleware(['role:admin|financeiro'])->group(function () {
                // Relatórios Financeiros
                Route::prefix('reports')->name('reports.')->group(function () {
                    Route::get('/', [FinanceiroReportController::class, 'index'])->name('index');
                    Route::get('/boletos', [FinanceiroReportController::class, 'boletos'])->name('boletos');
                    Route::get('/boletos/pdf', [FinanceiroReportController::class, 'downloadBoletosPDF'])->name('boletos.pdf');
                    Route::get('/boletos/stream', [FinanceiroReportController::class, 'streamBoletosPDF'])->name('boletos.stream');
                    Route::get('/credit-cards', [FinanceiroReportController::class, 'creditCards'])->name('credit-cards');
                    Route::get('/credit-cards/pdf', [FinanceiroReportController::class, 'downloadCreditCardsPDF'])->name('credit-cards.pdf');
                    Route::get('/credit-cards/stream', [FinanceiroReportController::class, 'streamCreditCardsPDF'])->name('credit-cards.stream');
                    Route::get('/receivables', [FinanceiroReportController::class, 'receivables'])->name('receivables');
                    Route::get('/receivables/pdf', [FinanceiroReportController::class, 'downloadReceivablesPDF'])->name('receivables.pdf');
                    Route::get('/cash-flow', [FinanceiroReportController::class, 'cashFlow'])->name('cash-flow');
                    Route::get('/cash-flow/pdf', [FinanceiroReportController::class, 'downloadCashFlowPDF'])->name('cash-flow.pdf');
                    Route::get('/cash-flow/stream', [FinanceiroReportController::class, 'streamCashFlowPDF'])->name('cash-flow.stream');
                    Route::get('/dailies', [FinanceiroReportController::class, 'dailies'])->name('dailies');
                    Route::get('/dailies/pdf', [FinanceiroReportController::class, 'downloadDailiesPDF'])->name('dailies.pdf');
                    Route::get('/commissions', [FinanceiroReportController::class, 'commissions'])->name('commissions');
                    Route::get('/commissions/pdf', [FinanceiroReportController::class, 'downloadCommissionsPDF'])->name('commissions.pdf');
                });
            });
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas do Consultor
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin|consultor'])
        ->prefix('consultor')
        ->name('consultor.')
        ->group(function () {
            Route::get('/dashboard', [ConsultorDashboardController::class, 'index'])->name('dashboard');
            Route::get('/clients', [ConsultorDashboardController::class, 'clients'])->name('clients');
            Route::get('/clients/{user}', [ConsultorDashboardController::class, 'showClient'])->name('clients.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas do Gerente
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin|gerente'])
        ->prefix('gerente')
        ->name('gerente.')
        ->group(function () {
            Route::get('/dashboard', [GerenteDashboardController::class, 'index'])->name('dashboard');
            Route::get('/team', [GerenteDashboardController::class, 'team'])->name('team');
            Route::get('/team/{employee}', [GerenteDashboardController::class, 'showTeamMember'])->name('team.show');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas do Funcionário
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin|funcionario'])
        ->prefix('funcionario')
        ->name('funcionario.')
        ->group(function () {
            Route::get('/dashboard', function () {
                return app(DashboardController::class)->funcionarioDashboard(auth()->user());
            })->name('dashboard');

            // Meus Boletos
            Route::get('/boletos', [FuncionarioBoletoController::class, 'index'])->name('boletos');
            Route::get('/boletos/{boleto}', [FuncionarioBoletoController::class, 'show'])->name('boletos.show');
            Route::get('/boletos/{boleto}/pdf', [FuncionarioBoletoController::class, 'downloadPDF'])->name('boletos.pdf');

            // Minha Folha de Pagamento
            Route::get('/payroll', [FuncionarioPayrollController::class, 'index'])->name('payroll');
            Route::get('/payroll/{payroll}', [FuncionarioPayrollController::class, 'show'])->name('payroll.show');
            Route::get('/payroll/{payroll}/payslip', [FuncionarioPayrollController::class, 'downloadPayslip'])->name('payroll.payslip');
    });

    /*
    |--------------------------------------------------------------------------
    | API Routes (para chamadas AJAX/Alpine.js)
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->name('api.')->group(function () {
        Route::middleware(['role:admin|rh|gerente'])->group(function () {
            Route::get('/employees/search', [EmployeeController::class, 'search'])->name('employees.search');
            Route::get('/departments', [EmployeeController::class, 'departments'])->name('departments.list');
        });

        Route::middleware(['role:admin|financeiro'])->group(function () {
            Route::get('/users/{user}', function (\App\Models\User $user) {
                return response()->json([
                    'id' => $user->id,
                    'name' => $user->name,
                    'cpf' => $user->cpf,
                    'email' => $user->email,
                ]);
            })->name('users.show');

            Route::get('/boletos/stats', [FinanceiroReportController::class, 'stats'])->name('boletos.stats');
        });
    });
});
