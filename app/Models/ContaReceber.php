<?php
// app/Models/ContaReceber.php

namespace App\Models;

use Illuminate\Database\Eloquent\{Builder, Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\{Boleto, CreditCardTransaction, Fatura, User};
use App\Traits\LogsActivityCustom;

class ContaReceber extends Model
{
    use SoftDeletes, LogsActivityCustom;

    protected $table = 'contas_receber';

    protected $fillable = [
        'cliente_id', 'boleto_id', 'credit_card_transaction_id',
        'created_by', 'received_by', 'consultant_id',
        'numero_documento', 'numero_parcela',
        'tipo', 'categoria', 'forma_pagamento',
        'cliente_nome', 'cliente_documento', 'cliente_email',
        'cliente_telefone', 'cliente_endereco',
        'valor_original', 'valor_desconto', 'valor_multa', 'valor_juros',
        'valor_acrescimos', 'valor_total', 'valor_recebido',
        'valor_taxa', 'valor_liquido',
        'has_commission', 'commission_percentage', 'commission_amount',
        'commission_paid', 'commission_paid_date',
        'parcela_atual', 'total_parcelas', 'fatura_id',
        'is_recurring', 'recurrence_rule', 'recurrence_start',
        'recurrence_end', 'recurrence_count',
        'data_emissao', 'data_vencimento', 'data_competencia',
        'data_recebimento', 'data_conciliacao', 'data_previsao_recebimento',
        'status', 'status_motivo',
        'enviou_cobranca', 'ultima_cobranca', 'tentativas_cobranca',
        'historico_cobranca',
        'banco_codigo', 'banco_nome', 'agencia', 'conta', 'pix_chave',
        'codigo_barras', 'linha_digitavel', 'qr_code_pix',
        'descricao', 'observacoes',
        'boleto_pdf_path', 'comprovante_path', 'nota_fiscal_path',
        'anexos',
        'centro_custo', 'codigo_orcamentario', 'priority',
        'tags', 'metadata',
    ];

    protected $casts = [
        'valor_original' => 'decimal:2',
        'valor_desconto' => 'decimal:2',
        'valor_multa' => 'decimal:2',
        'valor_juros' => 'decimal:2',
        'valor_acrescimos' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'valor_recebido' => 'decimal:2',
        'valor_taxa' => 'decimal:2',
        'valor_liquido' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'has_commission' => 'boolean',
        'commission_paid' => 'boolean',
        'is_recurring' => 'boolean',
        'enviou_cobranca' => 'boolean',
        'data_emissao' => 'date',
        'data_vencimento' => 'date',
        'data_competencia' => 'date',
        'data_recebimento' => 'date',
        'data_conciliacao' => 'date',
        'data_previsao_recebimento' => 'date',
        'commission_paid_date' => 'date',
        'ultima_cobranca' => 'date',
        'recurrence_start' => 'date',
        'recurrence_end' => 'date',
        'anexos' => 'json',
        'tags' => 'json',
        'metadata' => 'json',
        'tentativas_cobranca' => 'integer',
        'parcela_atual' => 'integer',
        'total_parcelas' => 'integer',
        'recurrence_count' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::creating(function (ContaReceber $conta) {
            if (!$conta->numero_documento) {
                $conta->numero_documento = self::generateDocumentNumber();
            }

            if (!$conta->valor_total) {
                $conta->valor_total = $conta->valor_original
                    - $conta->valor_desconto
                    + $conta->valor_multa
                    + $conta->valor_juros
                    + $conta->valor_acrescimos;
            }
        });

        // Atualizar status automaticamente baseado na data de vencimento
        static::retrieved(function (ContaReceber $conta) {
            if (in_array($conta->status, ['pendente', 'enviado', 'a_vencer'])
                && $conta->data_vencimento->isPast()) {
                // Não altera automaticamente - deve ser feito via comando agendado
            }
        });
    }

    /**
     * Cliente devedor.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    /**
     * Boleto relacionado.
     */
    public function boleto(): BelongsTo
    {
        return $this->belongsTo(Boleto::class);
    }

    /**
     * Transação de cartão relacionada.
     */
    public function creditCardTransaction(): BelongsTo
    {
        return $this->belongsTo(CreditCardTransaction::class);
    }

    /**
     * Usuário que criou.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuário que recebeu.
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Consultor responsável (para comissões).
     */
    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consultant_id');
    }

    /**
     * Fatura pai (parcelas).
     */
    public function fatura(): BelongsTo
    {
        return $this->belongsTo(self::class, 'fatura_id');
    }

    /**
     * Parcelas da fatura.
     */
    public function parcelas(): HasMany
    {
        return $this->hasMany(self::class, 'fatura_id');
    }

    /**
     * Verifica se está vencida.
     */
    public function isOverdue(): bool
    {
        return in_array($this->status, ['pendente', 'enviado', 'a_vencer', 'em_cobranca'])
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
     * Dias até o vencimento.
     */
    public function getDiasAteVencimentoAttribute(): int
    {
        if ($this->data_vencimento->isPast()) {
            return 0;
        }
        return now()->diffInDays($this->data_vencimento);
    }

    /**
     * Status formatado (label).
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendente' => 'Pendente',
            'enviado' => 'Enviado',
            'a_vencer' => 'A Vencer',
            'vencido' => 'Vencido',
            'recebido' => 'Recebido',
            'parcial' => 'Parcial',
            'cancelado' => 'Cancelado',
            'protestado' => 'Protestado',
            'negativado' => 'Negativado',
            'conciliado' => 'Conciliado',
            'em_cobranca' => 'Em Cobrança',
            default => ucfirst($this->status),
        };
    }

    /**
     * Cor do status para badge.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pendente', 'enviado' => 'yellow',
            'a_vencer' => 'blue',
            'vencido', 'protestado', 'negativado' => 'red',
            'recebido', 'conciliado' => 'green',
            'parcial' => 'orange',
            'cancelado' => 'gray',
            'em_cobranca' => 'purple',
            default => 'gray',
        };
    }

    /**
     * Calcular valor total com juros e multa se vencido.
     */
    public function getValorAtualizadoAttribute(): float
    {
        if (!$this->isOverdue()) {
            return $this->valor_total - $this->valor_desconto;
        }

        $diasAtraso = $this->dias_atraso;
        $multa = $this->valor_multa;
        $juros = $this->valor_juros * $diasAtraso;

        return $this->valor_original + $multa + $juros - $this->valor_desconto;
    }

    /**
     * Scope para contas a receber (abertas).
     */
    public function scopeAbertas(Builder $query)
    {
        return $query->whereIn('status', [
            'pendente', 'enviado', 'a_vencer', 'vencido', 'em_cobranca'
        ]);
    }

    /**
     * Scope para contas vencidas.
     */
    public function scopeVencidas(Builder $query)
    {
        return $query->whereIn('status', ['pendente', 'enviado', 'a_vencer', 'em_cobranca'])
            ->where('data_vencimento', '<', now()->startOfDay());
    }

    /**
     * Scope para contas a vencer.
     */
    public function scopeAVencer(Builder $query)
    {
        return $query->whereIn('status', ['pendente', 'enviado', 'a_vencer'])
            ->where('data_vencimento', '>=', now()->startOfDay());
    }

    /**
     * Scope para contas recebidas.
     */
    public function scopeRecebidas(Builder $query)
    {
        return $query->whereIn('status', ['recebido', 'conciliado']);
    }

    /**
     * Scope para contas que vencem em X dias.
     */
    public function scopeVencemEm(Builder $query, int $dias)
    {
        return $query->whereIn('status', ['pendente', 'enviado', 'a_vencer'])
            ->where('data_vencimento', '>=', now()->startOfDay())
            ->where('data_vencimento', '<=', now()->addDays($dias)->endOfDay());
    }

    /**
     * Scope para comissões pendentes.
     */
    public function scopeComissoesPendentes(Builder $query)
    {
        return $query->where('has_commission', true)
            ->where('commission_paid', false)
            ->whereIn('status', ['recebido', 'conciliado']);
    }

    /**
     * Gerar número de documento.
     */
    public static function generateDocumentNumber(): string
    {
        $prefix = 'CR';
        $year = date('Y');
        $month = date('m');
        $sequential = str_pad(
            self::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        return "{$prefix}-{$year}{$month}-{$sequential}";
    }

    /**
     * Marcar como recebido.
     */
    public function markAsReceived(float $valorRecebido, ?Carbon $dataRecebimento = null, ?int $receivedBy = null): void
    {
        $this->update([
            'status' => 'recebido',
            'valor_recebido' => $valorRecebido,
            'valor_liquido' => $valorRecebido - $this->valor_taxa,
            'data_recebimento' => $dataRecebimento ?? now(),
            'received_by' => $receivedBy ?? Auth::id(),
        ]);

        // Se tem comissão, calcular
        if ($this->has_commission && $this->commission_percentage) {
            $this->update([
                'commission_amount' => $valorRecebido * ($this->commission_percentage / 100),
            ]);
        }
    }

    /**
     * Enviar cobrança.
     */
    public function enviarCobranca(): void
    {
        $historico = $this->historico_cobranca ?? [];
        $historico[] = [
            'data' => now()->format('d/m/Y H:i'),
            'acao' => 'Cobrança enviada',
            'usuario' => Auth::user()->name,
        ];

        $this->update([
            'enviou_cobranca' => true,
            'ultima_cobranca' => now(),
            'tentativas_cobranca' => $this->tentativas_cobranca + 1,
            'historico_cobranca' => json_encode($historico),
            'status' => 'em_cobranca',
        ]);
    }

    /**
     * Aging de contas a receber (por faixa de atraso).
     */
    public static function getAging(): array
    {
        $contas = self::abertas()->get();

        return [
            'a_vencer' => [
                'count' => $contas->filter(fn($c) => $c->data_vencimento->isFuture())->count(),
                'total' => $contas->filter(fn($c) => $c->data_vencimento->isFuture())->sum('valor_total'),
            ],
            'vencidas_1_30' => [
                'count' => $contas->filter(fn($c) => $c->isOverdue() && $c->dias_atraso <= 30)->count(),
                'total' => $contas->filter(fn($c) => $c->isOverdue() && $c->dias_atraso <= 30)->sum('valor_total'),
            ],
            'vencidas_31_60' => [
                'count' => $contas->filter(fn($c) => $c->dias_atraso > 30 && $c->dias_atraso <= 60)->count(),
                'total' => $contas->filter(fn($c) => $c->dias_atraso > 30 && $c->dias_atraso <= 60)->sum('valor_total'),
            ],
            'vencidas_61_90' => [
                'count' => $contas->filter(fn($c) => $c->dias_atraso > 60 && $c->dias_atraso <= 90)->count(),
                'total' => $contas->filter(fn($c) => $c->dias_atraso > 60 && $c->dias_atraso <= 90)->sum('valor_total'),
            ],
            'vencidas_91_180' => [
                'count' => $contas->filter(fn($c) => $c->dias_atraso > 90 && $c->dias_atraso <= 180)->count(),
                'total' => $contas->filter(fn($c) => $c->dias_atraso > 90 && $c->dias_atraso <= 180)->sum('valor_total'),
            ],
            'vencidas_180_plus' => [
                'count' => $contas->filter(fn($c) => $c->dias_atraso > 180)->count(),
                'total' => $contas->filter(fn($c) => $c->dias_atraso > 180)->sum('valor_total'),
            ],
            'total_abertas' => [
                'count' => $contas->count(),
                'total' => $contas->sum('valor_total'),
            ],
        ];
    }
}
