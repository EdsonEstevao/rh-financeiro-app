<?php
// database/migrations/2024_01_01_000003_create_employee_documents_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();

            // Relacionamentos
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->onDelete('set null');

            // Informações do Documento
            $table->string('name', 200);
            $table->string('description', 500)->nullable();
            $table->enum('type', [
                'rg', 'cpf', 'ctps', 'pis_pasep', 'reservist',
                'voter_id', 'birth_certificate', 'marriage_certificate',
                'school_record', 'diploma', 'certificate',
                'resume', 'contract', 'ndas', 'medical',
                'photo', 'address_proof', 'bank_details',
                'health_plan', 'payroll', 'tax_declaration',
                'other'
            ]);
            $table->string('category', 50)->nullable(); // Categoria personalizada
            $table->string('file_path', 500);
            $table->string('file_name', 200);
            $table->string('file_extension', 10);
            $table->string('file_mime_type', 100);
            $table->unsignedBigInteger('file_size'); // em bytes
            $table->string('storage_disk', 50)->default('public'); // local, s3, etc

            // Datas
            $table->date('document_date')->nullable(); // Data do documento
            $table->date('expiration_date')->nullable(); // Data de validade
            $table->date('notification_date')->nullable(); // Data para notificar sobre vencimento

            // Status e Aprovação
            $table->enum('status', ['pending', 'approved', 'rejected', 'expired', 'archived'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            // Versionamento
            $table->integer('version')->default(1);
            $table->boolean('is_current')->default(true);
            $table->foreignId('previous_version_id')->nullable()->constrained('employee_documents')->onDelete('set null');

            // Tags e Metadados
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->text('notes')->nullable();

            // Visibilidade
            $table->boolean('is_private')->default(false);
            $table->boolean('requires_approval')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('type');
            $table->index('status');
            $table->index('expiration_date');
            $table->index(['employee_id', 'type']);
            $table->index(['employee_id', 'status']);
            $table->index('is_current');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
