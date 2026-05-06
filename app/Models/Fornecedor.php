<?php
// app/Models/Fornecedor.php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\HasMany;

use illuminate\Database\Query\Builder;

class Fornecedor extends Model
{
    use SoftDeletes;

    protected $table = 'fornecedores';

    protected $fillable = [
        'nome', 'nome_fantasia', 'documento', 'tipo_pessoa',
        'email', 'telefone', 'celular', 'endereco',
        'cidade', 'estado', 'cep',
        'contato_nome', 'contato_cargo',
        'banco_codigo', 'banco_nome', 'agencia', 'conta',
        'pix_chave', 'categoria', 'observacoes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Contas a pagar do fornecedor.
     */
    public function contasPagar(): HasMany
    {
        return $this->hasMany(ContaPagar::class);
    }

    /**
     * Scope para fornecedores ativos.
     */
    public function scopeAtivos(Builder $query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted document.
     */
    public function getDocumentoFormattedAttribute(): string
    {
        $document = preg_replace('/[^0-9]/', '', $this->documento);
        
        if (strlen($document) === 11) {
            // CPF: 000.000.000-00
            return vsprintf('%s%s%s.%s%s%s.%s%s%s-%s%s', str_split($document));
        }
        
        if (strlen($document) === 14) {
            // CNPJ: 00.000.000/0000-00
            return vsprintf('%s%s.%s%s%s.%s%s%s/%s%s%s%s-%s%s', str_split($document));
        }
        
        return $this->documento;
    }
}