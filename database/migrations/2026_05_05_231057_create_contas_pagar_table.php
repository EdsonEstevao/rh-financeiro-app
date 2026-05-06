<?php
// database/migrations/xxxx_xx_xx_create_contas_pagar_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contas_pagar', function (Blueprint $table) {
            $table->id();

            // Relacionamentos
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('paid_by')->nullable()->constrained('users')->onDelete('set null');

            // Identificação
            $table->string('numero_documento', 50)->unique();
            $table->string('numero_parcela', 10)->nullable();
            $table->string('nosso_numero', 50)->nullable();

            // Tipo e Categoria
            $table->enum('tipo', [
                'boleto', 'cartao_credito', 'transferencia', 'pix',
                'boleto_fatura', 'imposto', 'fornecedor', 'servico',
                'aluguel', 'condominio', 'energia', 'agua', 'telefone',
                'internet', 'seguro', 'outros'
            ]);
            $table->string('categoria', 50)->nullable();

            // Dados do Fornecedor/Beneficiário
            $table->string('beneficiario_nome', 200);
            $table->string('beneficiario_documento', 20)->nullable();
            $table->string('beneficiario_email', 100)->nullable();
            $table->string('beneficiario_telefone', 20)->nullable();
            $table->text('beneficiario_endereco')->nullable();

            // Dados Bancários (para pagamento)
            $table->string('banco_codigo', 10)->nullable();
            $table->string('banco_nome', 50)->nullable();
            $table->string('agencia', 20)->nullable();
            $table->string('conta', 20)->nullable();
            $table->string('pix_chave', 100)->nullable();
            $table->string('pix_tipo', 20)->nullable(); // cpf, cnpj, email, telefone, aleatoria

            // Valores
            $table->decimal('valor_original', 12, 2);
            $table->decimal('valor_desconto', 10, 2)->default(0);
            $table->decimal('valor_multa', 10, 2)->default(0);
            $table->decimal('valor_juros', 10, 2)->default(0);
            $table->decimal('valor_acrescimos', 10, 2)->default(0);
            $table->decimal('valor_total', 12, 2);
            $table->decimal('valor_pago', 12, 2)->nullable();

            // Datas
            $table->date('data_emissao');
            $table->date('data_vencimento');
            $table->date('data_competencia')->nullable(); // Mês de competência
            $table->date('data_pagamento')->nullable();
            $table->date('data_aprovacao')->nullable();
            $table->date('data_conciliacao')->nullable();

            // Parcelamento
            $table->integer('parcela_atual')->nullable();
            $table->integer('total_parcelas')->nullable();
            $table->foreignId('fatura_id')->nullable()->constrained('contas_pagar')->onDelete('set null');

            // Status
            $table->enum('status', [
                'pendente', 'aprovado', 'agendado', 'pago',
                'vencido', 'cancelado', 'protestado', 'conciliado'
            ])->default('pendente');
            $table->string('status_motivo', 255)->nullable();

            // Aprovação
            $table->boolean('requires_approval')->default(false);
            $table->enum('priority', ['baixa', 'media', 'alta', 'urgente'])->default('media');

            // Recorrência
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_rule', 50)->nullable();

            // Descrição
            $table->string('descricao', 200);
            $table->text('observacoes')->nullable();
            $table->text('instrucoes_pagamento')->nullable();

            // Arquivos
            $table->string('boleto_pdf_path', 500)->nullable();
            $table->string('comprovante_path', 500)->nullable();
            $table->json('anexos')->nullable();
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();

            // Centro de Custo
            $table->string('centro_custo', 50)->nullable();
            $table->string('codigo_orcamentario', 50)->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');

            // Campos para código de barras
            $table->string('codigo_barras', 100)->nullable();
            $table->string('linha_digitavel', 100)->nullable();

            // Auditoria
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('numero_documento');
            $table->index('status');
            $table->index('data_vencimento');
            $table->index('data_pagamento');
            $table->index('tipo');
            $table->index('categoria');
            $table->index('fornecedor_id');
            $table->index('priority');
            $table->index(['status', 'data_vencimento']);
            $table->index(['data_vencimento', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_pagar');
    }
};
