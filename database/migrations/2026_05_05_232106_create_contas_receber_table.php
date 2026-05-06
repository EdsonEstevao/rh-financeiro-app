<?php
// database/migrations/xxxx_xx_xx_create_contas_receber_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas_receber', function (Blueprint $table) {
            $table->id();

            // Relacionamentos
            $table->foreignId('cliente_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('boleto_id')->nullable()->constrained('boletos')->onDelete('set null');
            $table->foreignId('credit_card_transaction_id')->nullable()->constrained('credit_card_transactions')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('consultant_id')->nullable()->constrained('users')->onDelete('set null');

            // Identificação
            $table->string('numero_documento', 50)->unique();
            $table->string('numero_parcela', 10)->nullable();

            // Tipo e Categoria
            $table->enum('tipo', [
                'boleto', 'cartao_credito', 'cartao_debito', 'pix',
                'transferencia', 'dinheiro', 'cheque', 'nota_fiscal',
                'fatura', 'mensalidade', 'servico', 'produto',
                'comissao', 'aluguel', 'outros'
            ]);
            $table->string('categoria', 50)->nullable();
            $table->enum('forma_pagamento', [
                'boleto', 'cartao_credito', 'cartao_debito', 'pix',
                'transferencia', 'dinheiro', 'cheque', 'debito_automatico',
                'carteira_digital', 'outros'
            ])->nullable();

            // Dados do Cliente/Devedor
            $table->string('cliente_nome', 200);
            $table->string('cliente_documento', 20)->nullable();
            $table->string('cliente_email', 100)->nullable();
            $table->string('cliente_telefone', 20)->nullable();
            $table->text('cliente_endereco')->nullable();

            // Valores
            $table->decimal('valor_original', 12, 2);
            $table->decimal('valor_desconto', 10, 2)->default(0);
            $table->decimal('valor_multa', 10, 2)->default(0);
            $table->decimal('valor_juros', 10, 2)->default(0);
            $table->decimal('valor_acrescimos', 10, 2)->default(0);
            $table->decimal('valor_total', 12, 2);
            $table->decimal('valor_recebido', 12, 2)->nullable();
            $table->decimal('valor_taxa', 10, 2)->default(0); // Taxa de processamento
            $table->decimal('valor_liquido', 12, 2)->nullable(); // Valor líquido após taxas

            // Comissão
            $table->boolean('has_commission')->default(false);
            $table->decimal('commission_percentage', 5, 2)->nullable();
            $table->decimal('commission_amount', 10, 2)->nullable();
            $table->boolean('commission_paid')->default(false);
            $table->date('commission_paid_date')->nullable();

            // Parcelamento
            $table->integer('parcela_atual')->nullable();
            $table->integer('total_parcelas')->nullable();
            $table->foreignId('fatura_id')->nullable()->constrained('contas_receber')->onDelete('set null');

            // Recorrência
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule', 50)->nullable(); // monthly, yearly, etc
            $table->date('recurrence_start')->nullable();
            $table->date('recurrence_end')->nullable();
            $table->integer('recurrence_count')->nullable();

            // Datas
            $table->date('data_emissao');
            $table->date('data_vencimento');
            $table->date('data_competencia')->nullable();
            $table->date('data_recebimento')->nullable();
            $table->date('data_conciliacao')->nullable();
            $table->date('data_previsao_recebimento')->nullable(); // Data prevista para receber

            // Status
            $table->enum('status', [
                'pendente', 'enviado', 'a_vencer', 'vencido',
                'recebido', 'parcial', 'cancelado', 'protestado',
                'negativado', 'conciliado', 'em_cobranca'
            ])->default('pendente');
            $table->string('status_motivo', 255)->nullable();

            // Cobrança
            $table->boolean('enviou_cobranca')->default(false);
            $table->date('ultima_cobranca')->nullable();
            $table->integer('tentativas_cobranca')->default(0);
            $table->text('historico_cobranca')->nullable();

            // Dados Bancários para Recebimento
            $table->string('banco_codigo', 10)->nullable();
            $table->string('banco_nome', 50)->nullable();
            $table->string('agencia', 20)->nullable();
            $table->string('conta', 20)->nullable();
            $table->string('pix_chave', 100)->nullable();

            // Código de Barras / Linha Digitável (se boleto)
            $table->string('codigo_barras', 100)->nullable();
            $table->string('linha_digitavel', 100)->nullable();
            $table->string('qr_code_pix', 500)->nullable();

            // Descrição
            $table->string('descricao', 200);
            $table->text('observacoes')->nullable();

            // Arquivos
            $table->string('boleto_pdf_path', 500)->nullable();
            $table->string('comprovante_path', 500)->nullable();
            $table->string('nota_fiscal_path', 500)->nullable();
            $table->json('anexos')->nullable();

            // Classificação
            $table->string('centro_custo', 50)->nullable();
            $table->string('codigo_orcamentario', 50)->nullable();
            $table->enum('priority', ['baixa', 'media', 'alta', 'urgente'])->default('media');
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();

            // Auditoria
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('numero_documento');
            $table->index('status');
            $table->index('data_vencimento');
            $table->index('data_recebimento');
            $table->index('tipo');
            $table->index('categoria');
            $table->index('cliente_id');
            $table->index('consultant_id');
            $table->index('boleto_id');
            $table->index('forma_pagamento');
            $table->index('priority');
            $table->index('has_commission');
            $table->index('commission_paid');
            $table->index(['status', 'data_vencimento']);
            $table->index(['cliente_id', 'status']);
            $table->index(['consultant_id', 'status']);
            $table->index(['status', 'data_vencimento', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_receber');
    }
};
