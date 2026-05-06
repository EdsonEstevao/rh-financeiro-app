<?php
// app/Models/ContaPagar.php

namespace App\Models;

use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\{Department, Fornecedor, User};

class ContaPagar extends Model
{
    use SoftDeletes;

    protected $table = 'contas_pagar';

    protected $fillable = [
        'fornecedor_id', 'created_by', 'approved_by', 'paid_by',
        'numero_documento', 'numero_parcela', 'nosso_numero',
        'tipo', 'categoria',
        'beneficiario_nome', 'beneficiario_documento', 'beneficiario_email',
        'beneficiario_telefone', 'beneficiario_endereco',
        'banco_codigo', 'banco_nome', 'agencia', 'conta',
        'pix_chave', 'pix_tipo',
        'valor_original', 'valor_desconto', 'valor_multa', 'valor_juros',
        'valor_acrescimos', 'valor_total', 'valor_pago',
        'data_emissao', 'data_vencimento', 'data_competencia',
        'data_pagamento', 'data_aprovacao', 'data_conciliacao',
        'parcela_atual', 'total_parcelas', 'fatura_id',
        'status', 'status_motivo',
        'requires_approval', 'priority',
        'is_recurring', 'recurrence_rule',
        'descricao', 'observacoes', 'instrucoes_pagamento',
        'boleto_pdf_path', 'comprovante_path', 'anexos', 'tags', 'metadata',
        'centro_custo', 'codigo_orcamentario', 'department_id',
        'codigo_barras', 'linha_digitavel',
    ];

    protected $casts = [
        'valor_original' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'valor_multa' => 'decimal:2',
        'valor_juros' => 'decimal:2',
        'valor_acrescimos' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'valor_pago' => 'decimal:2',
        'data_emissao' => 'date',
        'data_vencimento' => 'date',
        'data_competencia' => 'date',
        'data_pagamento' => 'date',
        'data_aprovacao' => 'date',
        'data_conciliacao' => 'date',
        'requires_approval' => 'boolean',
        'is_recurring' => 'boolean',
        'anexos' => 'json',
        'tags' => 'json',
        'metadata' => 'json',
    ];

    /**
     * Fornecedor da conta.
     */
    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    /**
     * Usuário que criou a conta.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuário que aprovou a conta.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Usuário que efetuou o pagamento.
     */
    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    /**
     * Fatura pai (para parcelas).
     */
    public function fatura(): BelongsTo
    {
        return $this->belongsTo(self::class, 'fatura_id');
    }

    /**
     * Parcelas da fatura.
     */
    public function parcelas()
    {
        return $this->hasMany(self::class, 'fatura_id');
    }

    /**
     * Departamento responsável.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Verifica se está vencida.
     */
    public function isOverdue(): bool
    {
        return in_array($this->status, ['pendente', 'aprovado', 'agendado'])
            && $this->data_vencimento->isPast();
    }

    /**
     * Dias de atraso.
     */
    public function getDiasAtrasoAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->data_vencimento->diffInDays(now());
    }

    /**
     * Scope para contas pendentes.
     */
    public function scopePendentes(Builder $query)
    {
        return $query->whereIn('status', ['pendente', 'aprovado', 'agendado']);
    }

    /**
     * Scope para contas vencidas.
     */
    public function scopeVencidas($query)
    {
        return $query->whereIn('status', ['pendente', 'aprovado', 'agendado'])
            ->where('data_vencimento', '<', now());
    }

    /**
     * Scope para contas a vencer.
     */
    public function scopeAVencer($query)
    {
        return $query->whereIn('status', ['pendente', 'aprovado', 'agendado'])
            ->where('data_vencimento', '>=', now());
    }

    /**
     * Scope para contas pagas.
     */
    public function scopePagas($query)
    {
        return $query->where('status', 'pago');
    }

    /**
     * Scope para o mês atual.
     */
    public function scopeMesAtual($query)
    {
        return $query->whereMonth('data_vencimento', now()->month)
            ->whereYear('data_vencimento', now()->year);
    }
}
