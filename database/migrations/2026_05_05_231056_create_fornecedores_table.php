<?php
// database/migrations/xxxx_xx_xx_create_fornecedores_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 200);
            $table->string('nome_fantasia', 200)->nullable();
            $table->string('documento', 20)->unique(); // CPF/CNPJ
            $table->enum('tipo_pessoa', ['fisica', 'juridica'])->default('juridica');
            $table->string('email', 100)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->text('endereco')->nullable();
            $table->string('cidade', 100)->nullable();
            $table->string('estado', 2)->nullable();
            $table->string('cep', 10)->nullable();
            $table->string('contato_nome', 100)->nullable();
            $table->string('contato_cargo', 100)->nullable();
            $table->string('banco_codigo', 10)->nullable();
            $table->string('banco_nome', 50)->nullable();
            $table->string('agencia', 20)->nullable();
            $table->string('conta', 20)->nullable();
            $table->string('pix_chave', 100)->nullable();
            $table->string('categoria', 50)->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fornecedores');
    }
};
