<?php
// routes/web.php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{DashboardController, ProfileController};
use App\Http\Controllers\Admin\{AuditController, SettingsController, UserController};
use App\Http\Controllers\RH\{DocumentController, EmployeeAdvancedController, EmployeeController, PayrollController as RHPayrollController, ReportController as RHReportController, UserSearchController};
use App\Http\Controllers\Financeiro\{BoletoController, ContasPagarController, ContasReceberController, CreditCardController, FornecedorController, ReportController as FinanceiroReportController};
use App\Models\ContaReceber;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rota inicial
Route::get('/', function () {
    return redirect()->route('login');
});

// Rotas de autenticação (Breeze)
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Rotas Autenticadas
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard - Redireciona automaticamente pelo perfil
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Perfil do Usuário (Breeze)
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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
            Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

            // Gerenciamento de Usuários
            Route::resource('users', UserController::class);
            Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
            Route::post('users/{user}/restore', [UserController::class, 'restore'])->name('users.restore');

            // Auditoria
            Route::get('/audit', [AuditController::class, 'index'])->name('audit.index');
            Route::get('/audit/{activity}', [AuditController::class, 'show'])->name('audit.show');
            Route::get('/audit/export/pdf', [AuditController::class, 'exportPDF'])->name('audit.export.pdf');
            Route::get('/audit/export/excel', [AuditController::class, 'exportExcel'])->name('audit.export.excel');
            Route::post('/audit/clean', [AuditController::class, 'cleanOldRecords'])->name('audit.clean');

            // Configurações
            Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
            Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
            Route::post('/settings/backup', [SettingsController::class, 'backup'])->name('settings.backup');
            Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas do RH
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin,rh,gerente'])
        ->prefix('rh')
        ->name('rh.')
        ->group(function () {

            // Dashboard RH
            Route::get('/dashboard', [DashboardController::class, 'rh'])->name('dashboard');

            // Funcionários
            Route::resource('employees', EmployeeController::class);
            Route::post('employees/{employee}/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
            Route::get('employees/export/pdf', [EmployeeController::class, 'exportPDF'])->name('employees.export.pdf');

            // Rotas exclusivas do perfil RH
            Route::middleware(['role:admin,rh'])->group(function () {
                // Folha de Pagamento
                Route::resource('payroll', RHPayrollController::class);
                Route::post('payroll/process', [RHPayrollController::class, 'processMonthly'])->name('payroll.process');
                Route::get('payroll/{payroll}/payslip', [RHPayrollController::class, 'generatePayslip'])->name('payroll.payslip');

                // Documentos
                Route::resource('documents', DocumentController::class);
                Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
                Route::post('documents/{document}/approve', [DocumentController::class, 'approve'])->name('documents.approve');
                Route::post('documents/{document}/reject', [DocumentController::class, 'reject'])->name('documents.reject');

                // Relatórios
                Route::prefix('reports')->name('reports.')->group(function () {
                    Route::get('/', [RHReportController::class, 'index'])->name('index');
                    Route::get('/employees', [RHReportController::class, 'employees'])->name('employees');
                    Route::get('/employees/pdf', [RHReportController::class, 'downloadEmployeesPDF'])->name('employees.pdf');
                    Route::get('/employees/stream', [RHReportController::class, 'streamEmployeesPDF'])->name('employees.stream');
                    Route::get('/payroll', [RHReportController::class, 'payroll'])->name('payroll');
                    Route::get('/payroll/pdf', [RHReportController::class, 'downloadPayrollPDF'])->name('payroll.pdf');
                    Route::get('/payroll/stream', [RHReportController::class, 'streamPayrollPDF'])->name('payroll.stream');
                    Route::get('/attendance', [RHReportController::class, 'attendance'])->name('attendance');
                    Route::get('/benefits', [RHReportController::class, 'benefits'])->name('benefits');
                    Route::get('/terminations', [RHReportController::class, 'terminations'])->name('terminations');
                });

                 // Avançado (tela e update separado)
                Route::get('employees/{employee}/advanced', [EmployeeAdvancedController::class, 'edit'])
                    ->name('employees.advanced.edit');

                Route::put('employees/{employee}/advanced', [EmployeeAdvancedController::class, 'update'])
                    ->name('employees.advanced.update');

                // Autocomplete usuários
                Route::get('/rh/users/search', [UserSearchController::class, 'index'])
                            ->name('users.search');



            });
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas do Financeiro
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin,financeiro,consultor'])
        ->prefix('financeiro')
        ->name('financeiro.')
        ->group(function () {

            // Dashboard Financeiro
            Route::get('/dashboard', [DashboardController::class, 'financeiro'])->name('dashboard');

            // Boletos
            Route::resource('boletos', BoletoController::class);
            Route::get('boletos/{boleto}/pdf', [BoletoController::class, 'downloadPDF'])->name('boletos.pdf');
            Route::get('boletos/{boleto}/stream', [BoletoController::class, 'streamPDF'])->name('boletos.stream');
            Route::post('boletos/{boleto}/mark-paid', [BoletoController::class, 'markAsPaid'])->name('boletos.mark-paid');
            Route::post('boletos/{boleto}/cancel', [BoletoController::class, 'cancel'])->name('boletos.cancel');
            Route::post('boletos/{boleto}/send-email', [BoletoController::class, 'sendByEmail'])->name('boletos.send-email');

        /*
        |--------------------------------------------------------------------------
        | Cartões de Crédito
        |--------------------------------------------------------------------------
        */
        // Listar transações (GET)
        Route::get('/credit-cards', [CreditCardController::class, 'index'])
            ->name('credit-cards.index');

        // Mostrar formulário para nova transação (GET)
        Route::get('/credit-cards/create', [CreditCardController::class, 'create'])
            ->name('credit-cards.create');

        // Salvar nova transação (POST)
        Route::post('/credit-cards', [CreditCardController::class, 'store'])
            ->name('credit-cards.store');

        // Processar pagamento via formulário (POST)
        Route::post('/credit-cards/process', [CreditCardController::class, 'processPayment'])
            ->name('credit-cards.process');

        // Mostrar detalhes de uma transação (GET)
        Route::get('/credit-cards/{transaction}', [CreditCardController::class, 'show'])
            ->name('credit-cards.show');

        // Reembolsar transação (POST)
        Route::post('/credit-cards/{transaction}/refund', [CreditCardController::class, 'refund'])
            ->name('credit-cards.refund');

        // Download do comprovante (GET)
        Route::get('/credit-cards/{transaction}/receipt', [CreditCardController::class, 'generateReceipt'])
            ->name('credit-cards.receipt');


            // Rotas exclusivas do Financeiro
            Route::middleware(['role:admin,financeiro'])->group(function () {
                Route::prefix('reports')->name('reports.')->group(function () {
                    Route::get('/', [FinanceiroReportController::class, 'index'])->name('index');
                    Route::get('/boletos', [FinanceiroReportController::class, 'boletos'])->name('boletos');
                    Route::get('/boletos/pdf', [FinanceiroReportController::class, 'downloadBoletosPDF'])->name('boletos.pdf');
                    Route::get('/boletos/stream', [FinanceiroReportController::class, 'streamBoletosPDF'])->name('boletos.stream');
                    Route::get('/credit-cards', [FinanceiroReportController::class, 'creditCards'])->name('credit-cards');
                    Route::get('/credit-cards/pdf', [FinanceiroReportController::class, 'downloadCreditCardsPDF'])->name('credit-cards.pdf');
                    Route::get('/credit-cards/stream', [FinanceiroReportController::class, 'streamCreditCardsPDF'])->name('credit-cards.stream');
                    Route::get('/cash-flow', [FinanceiroReportController::class, 'cashFlow'])->name('cash-flow');
                    Route::get('/cash-flow/pdf', [FinanceiroReportController::class, 'downloadCashFlowPDF'])->name('cash-flow.pdf');
                    Route::get('/cash-flow/stream', [FinanceiroReportController::class, 'streamCashFlowPDF'])->name('cash-flow.stream');
                });
            });


        /*
        |--------------------------------------------------------------------------
        | Contas a Pagar
        |--------------------------------------------------------------------------
        */
        Route::resource('contas-pagar', ContasPagarController::class)
            ->parameters(['contas-pagar' => 'conta']);

        Route::post('contas-pagar/{conta}/mark-paid', [ContasPagarController::class, 'markAsPaid'])
            ->name('contas-pagar.mark-paid');

        Route::post('contas-pagar/{conta}/approve', [ContasPagarController::class, 'approve'])
            ->name('contas-pagar.approve');

        Route::post('contas-pagar/{conta}/cancel', [ContasPagarController::class, 'cancel'])
            ->name('contas-pagar.cancel');

        /*
        |--------------------------------------------------------------------------
        | Contas a Receber
        |--------------------------------------------------------------------------
        */
        Route::resource('contas-receber', ContasReceberController::class)
            ->parameters(['contas-receber' => 'conta']);

        Route::post('contas-receber/{conta}/mark-received', [ContasReceberController::class, 'markAsReceived'])
            ->name('contas-receber.mark-received');

        Route::post('contas-receber/{conta}/enviar-cobranca', [ContasReceberController::class, 'enviarCobranca'])
            ->name('contas-receber.enviar-cobranca');

        Route::post('contas-receber/{conta}/cancel', [ContasReceberController::class, 'cancel'])
            ->name('contas-receber.cancel');

        // API: Aging de contas a receber
        Route::get('contas-receber/report/aging', function () {
            return response()->json(ContaReceber::getAging());
        })->name('contas-receber.aging');

         /*
        |--------------------------------------------------------------------------
        | Fornecedores
        |--------------------------------------------------------------------------
        */
        // Fornecedores
        Route::resource('fornecedores', FornecedorController::class);
        
        Route::post('fornecedores/{fornecedor}/toggle-status', [FornecedorController::class, 'toggleStatus'])
            ->name('fornecedores.toggle-status');
        
        // API: Busca de fornecedores
        Route::get('api/fornecedores/search', [FornecedorController::class, 'search'])
            ->name('api.fornecedores.search');
        
        Route::get('api/fornecedores/{fornecedor}/details', [FornecedorController::class, 'getDetails'])
            ->name('api.fornecedores.details');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas do Consultor
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin,consultor'])
        ->prefix('consultor')
        ->name('consultor.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'consultor'])->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas do Gerente
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin,gerente'])
        ->prefix('gerente')
        ->name('gerente.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'gerente'])->name('dashboard');
    });

    /*
    |--------------------------------------------------------------------------
    | Rotas do Funcionário
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin,funcionario'])
        ->prefix('funcionario')
        ->name('funcionario.')
        ->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'funcionario'])->name('dashboard');
    });
});