<?php
// database/seeders/RoleAndPermissionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\{Permission, Role};

use App\Models\{Department, Employee, User};


class RoleAndPermissionSeederOld extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        /*
        |--------------------------------------------------------------------------
        | Definição de Permissões por Módulo
        |--------------------------------------------------------------------------
        */

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
        | Criação das Permissões no Banco de Dados
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
        | Criação dos Perfis (Roles) e Atribuição de Permissões
        |--------------------------------------------------------------------------
        */

        // 1. Admin - Acesso Total
        $admin = Role::findOrCreate('admin', 'web');
        $admin->givePermissionTo(Permission::all());
        $admin->description = 'Administrador do sistema com acesso total';
        $admin->save();

        // 2. RH - Recursos Humanos
        $rh = Role::findOrCreate('rh', 'web');
        $rh->givePermissionTo(array_merge(
            ['system.access'],
            $rhPermissions,
            ['funcionario.access', 'funcionario.dashboard.view'] // RH pode ver área de funcionário
        ));
        $rh->description = 'Profissional de Recursos Humanos';
        $rh->save();

        // 3. Financeiro
        $financeiro = Role::findOrCreate('financeiro', 'web');
        $financeiro->givePermissionTo(array_merge(
            ['system.access'],
            $financialPermissions,
            ['funcionario.access', 'funcionario.dashboard.view']
        ));
        $financeiro->description = 'Profissional do Financeiro';
        $financeiro->save();

        // 4. Consultor
        $consultor = Role::findOrCreate('consultor', 'web');
        $consultor->givePermissionTo(array_merge(
            ['system.access'],
            $consultorPermissions
        ));
        $consultor->description = 'Consultor de negócios';
        $consultor->save();

        // 5. Gerente
        $gerente = Role::findOrCreate('gerente', 'web');
        $gerente->givePermissionTo(array_merge(
            ['system.access'],
            $gerentePermissions
        ));
        $gerente->description = 'Gerente de departamento';
        $gerente->save();

        // 6. Funcionário
        $funcionario = Role::findOrCreate('funcionario', 'web');
        $funcionario->givePermissionTo(array_merge(
            ['system.access'],
            $funcionarioPermissions
        ));
        $funcionario->description = 'Funcionário padrão';
        $funcionario->save();

        /*
        |--------------------------------------------------------------------------
        | Criação de Usuários de Teste
        |--------------------------------------------------------------------------
        */
        if (app()->environment('local', 'development', 'testing')) {
            $this->createTestUsers();
        }
    }

    private function createTestUsers(): void
    {
        // Admin
        $admin = User::factory()->create([
            'name' => 'Administrador',
            'email' => 'admin@sistema.com',
            'password' => bcrypt('admin123'),
            'cpf' => '000.000.000-00',
            'phone' => '(11) 99999-0000',
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // RH
        $rh = User::factory()->create([
            'name' => 'Maria RH',
            'email' => 'rh@sistema.com',
            'password' => bcrypt('rh123'),
            'cpf' => '111.111.111-11',
            'phone' => '(11) 99999-1111',
            'email_verified_at' => now(),
        ]);
        $rh->assignRole('rh');

        // Criar registro de funcionário para o RH
        Employee::create([
            'user_id' => $rh->id,
            'department_id' => 1,
            'position' => 'Analista de RH Senior',
            'salary' => 8000.00,
            'hire_date' => '2020-01-15',
            'status' => 'active',
        ]);

        // Financeiro
        $financeiro = User::factory()->create([
            'name' => 'João Financeiro',
            'email' => 'financeiro@sistema.com',
            'password' => bcrypt('fin123'),
            'cpf' => '222.222.222-22',
            'phone' => '(11) 99999-2222',
            'email_verified_at' => now(),
        ]);
        $financeiro->assignRole('financeiro');

        Employee::create([
            'user_id' => $financeiro->id,
            'department_id' => 1,
            'position' => 'Analista Financeiro',
            'salary' => 7500.00,
            'hire_date' => '2021-03-10',
            'status' => 'active',
        ]);

        // Consultor
        $consultor = User::factory()->create([
            'name' => 'Carlos Consultor',
            'email' => 'consultor@sistema.com',
            'password' => bcrypt('con123'),
            'cpf' => '333.333.333-33',
            'phone' => '(11) 99999-3333',
            'email_verified_at' => now(),
        ]);
        $consultor->assignRole('consultor');

        // Gerente
        $gerente = User::factory()->create([
            'name' => 'Ana Gerente',
            'email' => 'gerente@sistema.com',
            'password' => bcrypt('ger123'),
            'cpf' => '444.444.444-44',
            'phone' => '(11) 99999-4444',
            'email_verified_at' => now(),
        ]);
        $gerente->assignRole('gerente');

        Employee::create([
            'user_id' => $gerente->id,
            'department_id' => 1,
            'position' => 'Gerente de TI',
            'salary' => 15000.00,
            'hire_date' => '2019-06-01',
            'status' => 'active',
        ]);

        // Funcionário
        $funcionario = User::factory()->create([
            'name' => 'Pedro Funcionário',
            'email' => 'funcionario@sistema.com',
            'password' => bcrypt('fun123'),
            'cpf' => '555.555.555-55',
            'phone' => '(11) 99999-5555',
            'email_verified_at' => now(),
        ]);
        $funcionario->assignRole('funcionario');

        Employee::create([
            'user_id' => $funcionario->id,
            'department_id' => 1,
            'position' => 'Desenvolvedor Full Stack',
            'salary' => 6000.00,
            'hire_date' => '2023-01-10',
            'status' => 'active',
        ]);

        // Criar departamentos de exemplo
        $departments = ['TI', 'RH', 'Financeiro', 'Marketing', 'Vendas', 'Operações'];
        foreach ($departments as $index => $name) {
            Department::create([
                'name' => $name,
                'description' => "Departamento de {$name}",
                'code' => strtoupper(substr($name, 0, 3)) . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);
        }
    }
}
