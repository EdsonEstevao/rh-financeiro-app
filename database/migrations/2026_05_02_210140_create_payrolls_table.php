<?php
// database/migrations/2024_01_01_000004_create_payrolls_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();

            // Relacionamentos
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');

            // Identificação
            $table->string('reference_number', 50)->unique(); // Número de referência
            $table->string('period', 7); // Formato: YYYY-MM
            $table->date('payment_date');
            $table->integer('year');
            $table->integer('month');
            $table->enum('type', ['monthly', 'thirteenth', 'vacation', 'bonus', 'advance', 'termination', 'overtime'])->default('monthly');

            // Informações do Funcionário (Snapshot)
            $table->string('employee_name', 100);
            $table->string('employee_cpf', 14);
            $table->string('employee_position', 100);
            $table->string('employee_department', 100);

            // Remuneração Base
            $table->decimal('base_salary', 12, 2);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->integer('worked_hours')->default(0);
            $table->integer('worked_days')->default(0);

            // Proventos (vem em forma de JSON para flexibilidade)
            $table->json('earnings')->nullable(); // Ex: [{"description": "Salário Base", "amount": 5000.00}]
            $table->decimal('total_earnings', 12, 2)->default(0);

            // Descontos (vem em forma de JSON para flexibilidade)
            $table->json('deductions')->nullable(); // Ex: [{"description": "INSS", "amount": 500.00}]
            $table->decimal('total_deductions', 12, 2)->default(0);

            // Benefícios
            $table->json('benefits')->nullable();
            $table->decimal('total_benefits', 10, 2)->default(0);

            // Adicionais
            $table->decimal('overtime_hours', 5, 2)->default(0);
            $table->decimal('overtime_amount', 10, 2)->default(0);
            $table->decimal('night_shift_hours', 5, 2)->default(0);
            $table->decimal('night_shift_amount', 10, 2)->default(0);
            $table->decimal('dangerousness_amount', 10, 2)->default(0);
            $table->decimal('unhealthiness_amount', 10, 2)->default(0);

            // Bônus e Comissões
            $table->decimal('bonus_amount', 10, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0);
            $table->json('commissions_details')->nullable();

            // Adiantamentos e Descontos
            $table->decimal('advance_amount', 10, 2)->default(0);
            $table->decimal('loan_amount', 10, 2)->default(0);

            // Encargos (Empresa)
            $table->decimal('fgts_amount', 10, 2)->default(0); // Fundo de Garantia
            $table->decimal('inss_employer_amount', 10, 2)->default(0); // INSS Patronal
            $table->decimal('total_charges', 10, 2)->default(0); // Total de encargos

            // Impostos
            $table->json('taxes')->nullable();
            $table->decimal('irrf_amount', 10, 2)->default(0);
            $table->decimal('inss_amount', 10, 2)->default(0);
            $table->decimal('total_taxes', 10, 2)->default(0);

            // Valores Finais
            $table->decimal('gross_salary', 12, 2); // Salário Bruto
            $table->decimal('net_salary', 12, 2); // Salário Líquido
            $table->decimal('total_cost', 12, 2)->default(0); // Custo total para empresa

            // Férias
            $table->decimal('vacation_amount', 10, 2)->default(0);
            $table->decimal('vacation_bonus', 10, 2)->default(0); // 1/3 de férias

            // 13º Salário
            $table->decimal('thirteenth_amount', 10, 2)->default(0);
            $table->integer('thirteenth_installment')->nullable(); // 1ª ou 2ª parcela

            // Rescisão
            $table->date('termination_date')->nullable();
            $table->enum('termination_type', ['resignation', 'dismissal_without_cause', 'dismissal_with_cause', 'mutual_agreement'])->nullable();
            $table->decimal('termination_amount', 12, 2)->nullable();
            $table->json('termination_details')->nullable();

            // Status
            $table->enum('status', ['draft', 'pending', 'processed', 'paid', 'cancelled', 'rejected'])->default('draft');
            $table->timestamp('processed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Auditoria
            $table->text('observations')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('reference_number');
            $table->index('period');
            $table->index('payment_date');
            $table->index(['employee_id', 'period']);
            $table->index(['department_id', 'period']);
            $table->index('status');
            $table->index('year');
            $table->index('month');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
