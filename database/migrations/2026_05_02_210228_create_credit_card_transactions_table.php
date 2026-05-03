<?php
// database/migrations/2024_01_01_000006_create_credit_card_transactions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_card_transactions', function (Blueprint $table) {
            $table->id();

            // Relacionamentos
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');

            // Identificação
            $table->string('transaction_id', 100)->unique(); // ID interno
            $table->string('gateway_transaction_id', 100)->nullable(); // ID do gateway
            $table->string('gateway_reference', 100)->nullable(); // Referência do gateway
            $table->string('authorization_code', 50)->nullable(); // Código de autorização da operadora
            $table->string('nsu', 50)->nullable(); // Número Sequencial Único

            // Valores
            $table->decimal('amount', 12, 2); // Valor total
            $table->decimal('original_amount', 12, 2); // Valor original (antes de taxas)
            $table->decimal('fee_amount', 10, 2)->default(0); // Taxa de processamento
            $table->decimal('discount_amount', 10, 2)->default(0); // Desconto aplicado
            $table->decimal('net_amount', 12, 2); // Valor líquido

            // Parcelamento
            $table->integer('installments')->default(1); // Número de parcelas
            $table->decimal('installment_amount', 10, 2)->nullable(); // Valor de cada parcela
            $table->boolean('is_installment')->default(false); // Indica se é parcelado
            $table->json('installments_details')->nullable(); // Detalhes das parcelas

            // Dados do Cartão (Mascarados - PCI Compliance)
            $table->string('card_holder_name', 200);
            $table->string('card_last_digits', 4)->nullable(); // Últimos 4 dígitos
            $table->string('card_bin', 6)->nullable(); // Primeiros 6 dígitos
            $table->enum('card_brand', ['visa', 'mastercard', 'amex', 'elo', 'hipercard', 'diners', 'discover', 'other']); // Marca do cartão
            $table->enum('card_type', ['credit', 'debit', 'prepaid'])->default('credit'); // Tipo do cartão
            $table->string('card_token', 200)->nullable(); // Token do cartão
            $table->integer('expiration_month')->nullable(); // Mês de expiração
            $table->integer('expiration_year')->nullable(); // Ano de expiração

            // Dados do Pagador
            $table->string('customer_name', 200); // Nome do cliente
            $table->string('customer_email', 100); // Email do cliente
            $table->string('customer_document', 20); // CPF/CNPJ do cliente
            $table->enum('customer_document_type', ['cpf', 'cnpj'])->default('cpf'); // Tipo do documento
            $table->string('customer_phone', 20)->nullable(); // Telefone do cliente
            $table->string('customer_ip', 45)->nullable(); // IPv4 ou IPv6

            // Endereço de Cobrança
            $table->string('billing_address', 200)->nullable(); // Logradouro
            $table->string('billing_city', 100)->nullable(); // Cidade
            $table->string('billing_state', 2)->nullable(); // Estado
            $table->string('billing_zip_code', 10)->nullable(); // CEP
            $table->string('billing_country', 50)->nullable()->default('BR'); // País

            // Descrição e Categorização
            $table->string('description', 200); // Descrição da transação
            $table->string('category', 50)->nullable(); // Categoria da transação (ex: e-commerce, assinatura, etc)
            $table->json('items')->nullable(); // Itens da compra
            $table->string('order_id', 100)->nullable(); // ID do pedido

            // Status
            $table->enum('status', [
                'pending', 'authorized', 'approved', 'captured',
                'rejected', 'refunded', 'partially_refunded',
                'cancelled', 'chargeback', 'dispute'
            ])->default('pending'); // Status da transação
            $table->string('status_reason', 255)->nullable(); // Motivo do status (ex: motivo da rejeição)
            $table->string('gateway_status', 50)->nullable(); // Status retornado pelo gateway

            // Datas
            $table->timestamp('authorized_at')->nullable(); // Data de autorização
            $table->timestamp('captured_at')->nullable(); // Data de captura
            $table->timestamp('refunded_at')->nullable(); // Data de reembolso
            $table->date('expected_payment_date')->nullable(); // Data prevista de pagamento

            // Reembolso
            $table->decimal('refunded_amount', 12, 2)->nullable(); // Valor reembolsado
            $table->string('refund_reason', 255)->nullable(); // Motivo do reembolso
            $table->string('refund_id', 100)->nullable(); // ID do reembolso

            // Chargeback/Disputa
            $table->boolean('has_chargeback')->default(false); // Indica se houve chargeback
            $table->decimal('chargeback_amount', 12, 2)->nullable(); // Valor do chargeback
            $table->date('chargeback_date')->nullable(); // Data do chargeback
            $table->string('chargeback_reason', 255)->nullable(); // Motivo do chargeback


            // Gateway de Pagamento
            $table->string('gateway', 50)->nullable(); // stone, cielo, rede, getnet, pagseguro, etc
            $table->json('gateway_request')->nullable(); // Dados enviados ao gateway
            $table->json('gateway_response')->nullable(); // Dados recebidos do gateway
            $table->text('gateway_error')->nullable(); // Erro retornado pelo gateway, se houver

            // Antifraude
            $table->decimal('fraud_score', 5, 2)->nullable(); // Pontuação de risco de fraude
            $table->boolean('fraud_approved')->nullable(); // Indica se a transação foi aprovada pelo sistema antifraude
            $table->json('antifraud_data')->nullable(); // Dados adicionais do sistema antifraude

            // Recorrência
            $table->boolean('is_recurring')->default(false); // Indica se é uma transação recorrente
            $table->string('recurrence_id', 100)->nullable(); // ID da recorrência
            $table->integer('recurrence_count')->nullable();  // Número de vezes que a recorrência foi processada

            // URL de retorno
            $table->string('callback_url', 500)->nullable(); // URL para callbacks do gateway
            $table->string('return_url', 500)->nullable(); // URL para redirecionamento após pagamento

            // Arquivos
            $table->string('receipt_path', 500)->nullable(); // Caminho para o recibo da transação
            $table->json('attachments')->nullable(); // Arquivos relacionados à transação (ex: comprovantes, faturas, etc)

            // Metadados e Observações
            $table->text('notes')->nullable(); // Observações internas
            $table->json('metadata')->nullable(); // Dados adicionais personalizados
            $table->json('custom_fields')->nullable(); // Campos personalizados para integrações

            // Auditoria
            $table->timestamps(); // Data de criação e atualização
            $table->softDeletes(); // Data de exclusão (soft delete)

            // Índices
            $table->index('transaction_id');
            $table->index('gateway_transaction_id');
            $table->index('status');
            $table->index('card_brand');
            $table->index('card_last_digits');
            $table->index('customer_document');
            $table->index('authorization_code');
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
            $table->index('gateway');
            $table->index('order_id');
            $table->index('is_recurring');
            $table->index('created_at');
            $table->index('captured_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_card_transactions');
    }
};
