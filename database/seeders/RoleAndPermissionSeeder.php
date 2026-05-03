<?php
// database/seeders/RoleAndPermissionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\{Permission, Role};

use App\Models\{Department, Employee, User};

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | 1. Criar Departamentos Primeiro
        |--------------------------------------------------------------------------
        */
        $this->createDepartments();

        /*
        |--------------------------------------------------------------------------
        | 2. Definição de Permissões por Módulo
        |--------------------------------------------------------------------------
        */

        // // Permissões do Sistema
        // $systemPermissions = [
        //     'system.access',
        //     'system.settings.view',
        //     'system.settings.edit',
        // ];

        // // Permissões de Administração
        // $adminPermissions = [
        //     'admin.access',
        //     'admin.dashboard.view',
        //     'admin.users.view',
        //     'admin.users.create',
        //     'admin.users.edit',
        //     'admin.users.delete',
        //     'admin.audit.view',
        // ];

        // // Permissões de RH
        // $rhPermissions = [
        //     'rh.access',
        //     'rh.dashboard.view',
        //     'rh.employees.view',
        //     'rh.employees.create',
        //     'rh.employees.edit',
        //     'rh.employees.delete',
        //     'rh.payroll.view',
        //     'rh.payroll.process',
        //     'rh.documents.view',
        //     'rh.documents.create',
        //     'rh.documents.approve',
        //     'rh.reports.view',
        //     'rh.reports.export',
        // ];

        // // Permissões de Financeiro
        // $financialPermissions = [
        //     'financeiro.access',
        //     'financeiro.dashboard.view',
        //     'financeiro.boletos.view',
        //     'financeiro.boletos.create',
        //     'financeiro.boletos.edit',
        //     'financeiro.boletos.delete',
        //     'financeiro.boletos.cancel',
        //     'financeiro.boletos.mark-paid',
        //     'financeiro.credit-cards.view',
        //     'financeiro.credit-cards.process',
        //     'financeiro.credit-cards.refund',
        //     'financeiro.reports.view',
        //     'financeiro.reports.export',
        // ];

        // // Permissões de Consultor
        // $consultorPermissions = [
        //     'consultor.access',
        //     'consultor.dashboard.view',
        //     'consultor.clients.view',
        //     'consultor.reports.view',
        // ];

        // // Permissões de Gerente
        // $gerentePermissions = [
        //     'gerente.access',
        //     'gerente.dashboard.view',
        //     'gerente.team.view',
        //     'gerente.rh.employees.view',
        //     'gerente.rh.payroll.view',
        //     'gerente.financeiro.reports.view',
        // ];

        // // Permissões de Funcionário
        // $funcionarioPermissions = [
        //     'funcionario.access',
        //     'funcionario.dashboard.view',
        //     'funcionario.payroll.view',
        //     'funcionario.payroll.download',
        //     'funcionario.boletos.view',
        //     'funcionario.boletos.download',
        //     'funcionario.documents.view',
        //     'funcionario.profile.view',
        //     'funcionario.profile.edit',
        // ];
         // 1. Permissões do Sistema
        $systemPermissions = [
            'system.access',
            'system.settings.view',
            'system.settings.edit',
            'system.backup.create',
            'system.backup.restore',
        ];

        // 2. Permissões de Administração
        $adminPermissions = [
            'admin.access',
            'admin.dashboard.view',
            'admin.users.view',
            'admin.users.create',
            'admin.users.edit',
            'admin.users.delete',
            'admin.users.restore',
            'admin.users.assign-roles',
            'admin.audit.view',
            'admin.audit.export',
            'admin.reports.view',
            'admin.reports.export',
        ];

        // 3. Permissões de RH
        $rhPermissions = [
            'rh.access',
            'rh.dashboard.view',
            'rh.employees.view',
            'rh.employees.create',
            'rh.employees.edit',
            'rh.employees.delete',
            'rh.employees.restore',
            'rh.employees.export',
            'rh.payroll.view',
            'rh.payroll.create',
            'rh.payroll.edit',
            'rh.payroll.delete',
            'rh.payroll.process',
            'rh.payroll.export',
            'rh.documents.view',
            'rh.documents.create',
            'rh.documents.edit',
            'rh.documents.delete',
            'rh.documents.approve',
            'rh.documents.reject',
            'rh.documents.download',
            'rh.reports.view',
            'rh.reports.export',
            'rh.reports.employees',
            'rh.reports.payroll',
            'rh.reports.attendance',
            'rh.reports.benefits',
            'rh.reports.terminations',
        ];

        // 4. Permissões de Financeiro
        $financialPermissions = [
            'financeiro.access',
            'financeiro.dashboard.view',
            'financeiro.boletos.view',
            'financeiro.boletos.create',
            'financeiro.boletos.edit',
            'financeiro.boletos.delete',
            'financeiro.boletos.cancel',
            'financeiro.boletos.mark-paid',
            'financeiro.boletos.send-email',
            'financeiro.boletos.download',
            'financeiro.boletos.export',
            'financeiro.credit-cards.view',
            'financeiro.credit-cards.create',
            'financeiro.credit-cards.process',
            'financeiro.credit-cards.refund',
            'financeiro.credit-cards.receipt',
            'financeiro.credit-cards.export',
            'financeiro.reports.view',
            'financeiro.reports.export',
            'financeiro.reports.boletos',
            'financeiro.reports.credit-cards',
            'financeiro.reports.receivables',
            'financeiro.reports.cash-flow',
            'financeiro.reports.dailies',
            'financeiro.reports.commissions',
        ];

        // 5. Permissões de Consultor
        $consultorPermissions = [
            'consultor.access',
            'consultor.dashboard.view',
            'consultor.clients.view',
            'consultor.clients.details',
            'consultor.boletos.view',
            'consultor.reports.view',
        ];

        // 6. Permissões de Gerente
        $gerentePermissions = [
            'gerente.access',
            'gerente.dashboard.view',
            'gerente.team.view',
            'gerente.team.details',
            'gerente.rh.employees.view',
            'gerente.rh.payroll.view',
            'gerente.financeiro.reports.view',
            'gerente.financeiro.boletos.view',
        ];

        // 7. Permissões de Funcionário
        $funcionarioPermissions = [
            'funcionario.access',
            'funcionario.dashboard.view',
            'funcionario.payroll.view',
            'funcionario.payroll.download',
            'funcionario.boletos.view',
            'funcionario.boletos.download',
            'funcionario.documents.view',
            'funcionario.documents.upload',
            'funcionario.profile.view',
            'funcionario.profile.edit',
        ];

        /*
        |--------------------------------------------------------------------------
        | 3. Criação das Permissões
        |--------------------------------------------------------------------------
        */
        $allPermissions = array_merge(
            $systemPermissions,
            $adminPermissions,
            $rhPermissions,
            $financialPermissions,
            $consultorPermissions,
            $gerentePermissions,
            $funcionarioPermissions
        );

        foreach ($allPermissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Criação dos Perfis (Roles)
        |--------------------------------------------------------------------------
        */

        // Admin - Acesso Total
        $admin = Role::findOrCreate('admin', 'web');
        $admin->syncPermissions(Permission::all());

        // RH - Recursos Humanos
        $rh = Role::findOrCreate('rh', 'web');
        $rh->syncPermissions(array_merge(
            ['system.access'],
            $rhPermissions,
            ['funcionario.access', 'funcionario.dashboard.view']
        ));

        // Financeiro
        $financeiro = Role::findOrCreate('financeiro', 'web');
        $financeiro->syncPermissions(array_merge(
            ['system.access'],
            $financialPermissions,
            ['funcionario.access', 'funcionario.dashboard.view']
        ));

        // Consultor
        $consultor = Role::findOrCreate('consultor', 'web');
        $consultor->syncPermissions(array_merge(
            ['system.access'],
            $consultorPermissions
        ));

        // Gerente
        $gerente = Role::findOrCreate('gerente', 'web');
        $gerente->syncPermissions(array_merge(
            ['system.access'],
            $gerentePermissions
        ));

        // Funcionário
        $funcionario = Role::findOrCreate('funcionario', 'web');
        $funcionario->syncPermissions(array_merge(
            ['system.access'],
            $funcionarioPermissions
        ));

        /*
        |--------------------------------------------------------------------------
        | 5. Criação de Usuários de Teste
        |--------------------------------------------------------------------------
        */
        if (app()->environment('local', 'development', 'testing')) {
            $this->createTestUsers();
        }
    }

    /**
     * Criar departamentos base do sistema
     */
    private function createDepartments(): void
    {
        $departments = [
            [
                'name' => 'Recursos Humanos',
                'code' => 'RH',
                'description' => 'Departamento de Recursos Humanos',
                'budget' => 200000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Financeiro',
                'code' => 'FIN',
                'description' => 'Departamento Financeiro',
                'budget' => 300000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Tecnologia da Informação',
                'code' => 'TI',
                'description' => 'Departamento de Tecnologia da Informação',
                'budget' => 500000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Marketing',
                'code' => 'MKT',
                'description' => 'Departamento de Marketing',
                'budget' => 250000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Vendas',
                'code' => 'VEN',
                'description' => 'Departamento de Vendas',
                'budget' => 400000.00,
                'is_active' => true,
            ],
            [
                'name' => 'Operações',
                'code' => 'OPE',
                'description' => 'Departamento de Operações',
                'budget' => 350000.00,
                'is_active' => true,
            ],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['code' => $department['code']],
                $department
            );
        }
    }

    /**
     * Criar usuários de teste
     */
    private function createTestUsers(): void
    {
        // Buscar departamentos criados
        $rhDepartment = Department::where('code', '=',  'RH')->first();
        $finDepartment = Department::where('code', '=', 'FIN')->first();
        $tiDepartment = Department::where('code', '=', 'TI')->first();

        // 1. Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@sistema.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('admin123'),
                'cpf' => '000.000.000-00',
                'phone' => '(11) 99999-0000',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        // 2. RH
        $rh = User::firstOrCreate(
            ['email' => 'rh@sistema.com'],
            [
                'name' => 'Maria RH',
                'password' => bcrypt('rh123'),
                'cpf' => '111.111.111-11',
                'phone' => '(11) 99999-1111',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $rh->assignRole('rh');

        // Criar funcionário para o RH
        if ($rhDepartment) {
            Employee::firstOrCreate(
                ['user_id' => $rh->id],
                [
                    'department_id' => $rhDepartment->id,
                    'position' => 'Analista de RH Senior',
                    'salary' => 8000.00,
                    'hire_date' => '2020-01-15',
                    'status' => 'active',
                ]
            );
        }

        // 3. Financeiro
        $financeiro = User::firstOrCreate(
            ['email' => 'financeiro@sistema.com'],
            [
                'name' => 'João Financeiro',
                'password' => bcrypt('fin123'),
                'cpf' => '222.222.222-22',
                'phone' => '(11) 99999-2222',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $financeiro->assignRole('financeiro');

        // Criar funcionário para o Financeiro
        if ($finDepartment) {
            Employee::firstOrCreate(
                ['user_id' => $financeiro->id],
                [
                    'department_id' => $finDepartment->id,
                    'position' => 'Analista Financeiro',
                    'salary' => 7500.00,
                    'hire_date' => '2021-03-10',
                    'status' => 'active',
                ]
            );
        }

        // 4. Consultor
        $consultor = User::firstOrCreate(
            ['email' => 'consultor@sistema.com'],
            [
                'name' => 'Carlos Consultor',
                'password' => bcrypt('con123'),
                'cpf' => '333.333.333-33',
                'phone' => '(11) 99999-3333',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $consultor->assignRole('consultor');

        // 5. Gerente
        $gerente = User::firstOrCreate(
            ['email' => 'gerente@sistema.com'],
            [
                'name' => 'Ana Gerente',
                'password' => bcrypt('ger123'),
                'cpf' => '444.444.444-44',
                'phone' => '(11) 99999-4444',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $gerente->assignRole('gerente');

        // Criar funcionário para o Gerente
        if ($tiDepartment) {
            Employee::firstOrCreate(
                ['user_id' => $gerente->id],
                [
                    'department_id' => $tiDepartment->id,
                    'position' => 'Gerente de TI',
                    'salary' => 15000.00,
                    'hire_date' => '2019-06-01',
                    'status' => 'active',
                ]
            );
        }

        // 6. Funcionário
        $funcionario = User::firstOrCreate(
            ['email' => 'funcionario@sistema.com'],
            [
                'name' => 'Pedro Funcionário',
                'password' => bcrypt('fun123'),
                'cpf' => '555.555.555-55',
                'phone' => '(11) 99999-5555',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
        $funcionario->assignRole('funcionario');

        // Criar funcionário
        if ($tiDepartment) {
            Employee::firstOrCreate(
                ['user_id' => $funcionario->id],
                [
                    'department_id' => $tiDepartment->id,
                    'position' => 'Desenvolvedor Full Stack',
                    'salary' => 6000.00,
                    'hire_date' => '2023-01-10',
                    'status' => 'active',
                ]
            );
        }

        // Exibir resumo
        $this->command->info('✅ Usuários de teste criados com sucesso!');
        $this->command->info('───────────────────────────────────────────');
        $this->command->info('📧 Credenciais de Acesso:');
        $this->command->info('───────────────────────────────────────────');
        $this->command->info('Admin:       admin@sistema.com / admin123');
        $this->command->info('RH:          rh@sistema.com / rh123');
        $this->command->info('Financeiro:  financeiro@sistema.com / fin123');
        $this->command->info('Consultor:   consultor@sistema.com / con123');
        $this->command->info('Gerente:     gerente@sistema.com / ger123');
        $this->command->info('Funcionário: funcionario@sistema.com / fun123');
        $this->command->info('───────────────────────────────────────────');
    }
}
