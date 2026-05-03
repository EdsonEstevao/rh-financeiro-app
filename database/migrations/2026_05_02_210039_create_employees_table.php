<?php
// database/migrations/2024_01_01_000002_create_employees_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Relacionamentos
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('supervisor_id')->nullable()->constrained('employees')->onDelete('set null');

            // Informações Profissionais
            $table->string('registration_number', 50)->unique()->nullable(); // Matrícula
            $table->string('position', 100); // Cargo
            $table->string('role', 100)->nullable(); // Função
            $table->enum('employment_type', ['clt', 'pj', 'intern', 'temporary', 'contractor'])->default('clt');
            $table->enum('work_schedule', ['full_time', 'part_time', 'flexible', 'remote'])->default('full_time');
            $table->integer('workload_hours')->default(40); // Horas semanais

            // Informações Salariais
            $table->decimal('salary', 12, 2);
            $table->string('salary_type', 20)->default('monthly'); // monthly, hourly, daily
            $table->decimal('benefits_value', 12, 2)->nullable()->default(0);
            $table->json('salary_history')->nullable(); // Histórico de alterações salariais

            // Datas
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->date('probation_end_date')->nullable(); // Fim do período de experiência
            $table->date('last_promotion_date')->nullable();

            // Informações Pessoais
            $table->string('rg', 20)->nullable();
            $table->string('issuer', 20)->nullable(); // Órgão emissor do RG
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed', 'stable_union'])->nullable();
            $table->string('nationality', 50)->nullable()->default('Brasileiro(a)');
            $table->string('birth_place', 100)->nullable();

            // Contato
            $table->string('personal_email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip_code', 10)->nullable();

            // Contato de Emergência
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_relationship', 50)->nullable();

            // Informações Bancárias
            $table->string('bank_name', 50)->nullable();
            $table->string('bank_code', 10)->nullable();
            $table->string('agency', 20)->nullable();
            $table->string('account', 20)->nullable();
            $table->string('account_type', 20)->nullable(); // corrente, poupança
            $table->string('pix_key', 100)->nullable();

            // Dados Fiscais
            $table->string('pis_pasep', 20)->nullable();
            $table->string('ctps', 20)->nullable(); // Carteira de Trabalho
            $table->string('ctps_serie', 20)->nullable();
            $table->string('voter_id', 20)->nullable(); // Título de eleitor
            $table->string('military_id', 20)->nullable(); // Reservista

            // Documentos
            $table->string('photo_url', 255)->nullable();
            $table->boolean('has_dependents')->default(false);
            $table->json('dependents_info')->nullable();

            // Educação
            $table->enum('education_level', [
                'elementary', 'high_school', 'technical',
                'bachelor', 'postgraduate', 'master', 'doctorate'
            ])->nullable();
            $table->string('institution', 100)->nullable();
            $table->string('course', 100)->nullable();
            $table->year('graduation_year')->nullable();

            // Benefícios
            $table->boolean('has_health_plan')->default(false);
            $table->boolean('has_dental_plan')->default(false);
            $table->boolean('has_life_insurance')->default(false);
            $table->boolean('has_meal_voucher')->default(false);
            $table->boolean('has_food_voucher')->default(false);
            $table->boolean('has_transportation_voucher')->default(false);
            $table->boolean('has_gym_pass')->default(false);
            $table->decimal('meal_voucher_value', 10, 2)->nullable();
            $table->decimal('food_voucher_value', 10, 2)->nullable();
            $table->decimal('transportation_voucher_value', 10, 2)->nullable();

            // Status e Períodos
            $table->enum('status', ['active', 'inactive', 'vacation', 'leave', 'terminated', 'suspended'])->default('active');
            $table->date('vacation_start_date')->nullable();
            $table->date('vacation_end_date')->nullable();
            $table->integer('vacation_days_available')->default(30);
            $table->integer('sick_days_available')->default(15);

            // Avaliações
            $table->date('last_evaluation_date')->nullable();
            $table->decimal('last_evaluation_score', 3, 2)->nullable();
            $table->text('evaluation_comments')->nullable();

            // Observações e Metadados
            $table->text('observations')->nullable();
            $table->json('skills')->nullable(); // Habilidades
            $table->json('certifications')->nullable(); // Certificações
            $table->json('languages')->nullable(); // Idiomas
            $table->json('metadata')->nullable(); // Campos extras

            // Auditoria
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('registration_number');
            $table->index('status');
            $table->index('hire_date');
            $table->index('department_id');
            $table->index('employment_type');
            $table->index(['department_id', 'status']);
            $table->index(['department_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};