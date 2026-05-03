<?php
// database/migrations/2024_01_01_000005_create_boletos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boletos', function (Blueprint $table) {
            $table->id();

            // Relacionamentos
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            // Identificação
            $table->string('boleto_number', 50)->unique(); // Número interno
            $table->string('our_number', 50)->unique()->nullable(); // Nosso número
            $table->string('document_number', 50)->nullable(); // Número do documento
            $table->string('contract_number', 50)->nullable(); // Número do contrato

            // Dados do Pagador
            $table->string('payer_name', 200);
            $table->string('payer_document', 20); // CPF/CNPJ
            $table->enum('payer_document_type', ['cpf', 'cnpj'])->default('cpf');
            $table->string('payer_email', 100)->nullable();
            $table->string('payer_phone', 20)->nullable();
            $table->text('payer_address')->nullable();
            $table->string('payer_city', 100)->nullable();
            $table->string('payer_state', 2)->nullable();
            $table->string('payer_zip_code', 10)->nullable();

            // Dados do Beneficiário
            $table->string('beneficiary_name', 200)->nullable();
            $table->string('beneficiary_document', 20)->nullable();
            $table->text('beneficiary_address')->nullable();

            // Valores
            $table->decimal('amount', 12, 2); // Valor principal
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->date('discount_limit_date')->nullable(); // Data limite para desconto
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->decimal('fine_percentage', 5, 2)->default(2.00); // % de multa
            $table->decimal('interest_amount', 10, 2)->default(0);
            $table->decimal('interest_percentage', 5, 2)->default(1.00); // % de juros ao mês
            $table->decimal('other_charges', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2); // Valor total

            // Datas
            $table->date('issue_date'); // Data de emissão
            $table->date('due_date'); // Data de vencimento
            $table->date('processed_at')->nullable();
            $table->date('paid_at')->nullable();
            $table->date('cancelled_at')->nullable();

            // Dados Bancários
            $table->string('bank_code', 10)->nullable(); // Código do banco
            $table->string('bank_name', 50)->nullable();
            $table->string('agency', 20)->nullable();
            $table->string('account', 20)->nullable();
            $table->string('wallet', 10)->nullable(); // Carteira
            $table->string('agreement_number', 50)->nullable(); // Convênio

            // Código de Barras e Linha Digitável
            $table->string('barcode', 60)->nullable();
            $table->string('digitable_line', 60)->nullable();

            // Descrição e Instruções
            $table->string('description', 200);
            $table->text('instructions')->nullable();
            $table->text('additional_info')->nullable();

            // Status
            $table->enum('status', [
                'draft', 'pending', 'registered', 'paid',
                'overdue', 'cancelled', 'protested', 'returned'
            ])->default('draft');
            $table->string('status_reason', 255)->nullable();

            // Baixa
            $table->decimal('paid_amount', 12, 2)->nullable();
            $table->date('credit_date')->nullable(); // Data do crédito

            // Remessa e Retorno (CNAB)
            $table->string('remessa_file', 500)->nullable();
            $table->string('retorno_file', 500)->nullable();
            $table->json('cnab_data')->nullable();

            // Notificações
            $table->boolean('email_sent')->default(false);
            $table->timestamp('email_sent_at')->nullable();
            $table->boolean('sms_sent')->default(false);
            $table->timestamp('sms_sent_at')->nullable();
            $table->integer('days_overdue_notification')->default(0);

            // Recorrência
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule', 50)->nullable(); // monthly, yearly, etc
            $table->date('recurrence_start')->nullable();
            $table->date('recurrence_end')->nullable();
            $table->integer('recurrence_count')->nullable();
            $table->foreignId('parent_boleto_id')->nullable()->constrained('boletos')->onDelete('set null');

            // Tags e Categorização
            $table->string('category', 50)->nullable();
            $table->json('tags')->nullable();
            $table->string('reference', 100)->nullable(); // Referência externa

            // Arquivos Anexos
            $table->string('pdf_path', 500)->nullable();
            $table->json('attachments')->nullable();

            // Metadados e Observações
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            // Auditoria
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('boleto_number');
            $table->index('our_number');
            $table->index('status');
            $table->index('due_date');
            $table->index('issue_date');
            $table->index('paid_at');
            $table->index(['user_id', 'status']);
            $table->index(['status', 'due_date']);
            $table->index('payer_document');
            $table->index('bank_code');
            $table->index('category');
            $table->index('is_recurring');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletos');
    }
};
