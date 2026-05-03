<?php
// app/Models/CreditCardTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCardTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'created_by',
        'transaction_id',
        'gateway_transaction_id',
        'gateway_reference',
        'authorization_code',
        'nsu',
        'amount',
        'original_amount',
        'fee_amount',
        'discount_amount',
        'net_amount',
        'installments',
        'installment_amount',
        'is_installment',
        'installments_details',
        'card_holder_name',
        'card_last_digits',
        'card_bin',
        'card_brand',
        'card_type',
        'card_token',
        'expiration_month',
        'expiration_year',
        'customer_name',
        'customer_email',
        'customer_document',
        'customer_phone',
        'customer_ip',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_zip_code',
        'billing_country',
        'description',
        'category',
        'items',
        'order_id',
        'status',
        'status_reason',
        'gateway_status',
        'authorized_at',
        'captured_at',
        'refunded_at',
        'expected_payment_date',
        'refunded_amount',
        'refund_reason',
        'refund_id',
        'has_chargeback',
        'chargeback_amount',
        'chargeback_date',
        'chargeback_reason',
        'gateway',
        'gateway_request',
        'gateway_response',
        'gateway_error',
        'fraud_score',
        'fraud_approved',
        'antifraud_data',
        'is_recurring',
        'recurrence_id',
        'recurrence_count',
        'callback_url',
        'return_url',
        'receipt_path',
        'attachments',
        'notes',
        'metadata',
        'custom_fields',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'installment_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'chargeback_amount' => 'decimal:2',
        'installments' => 'integer',
        'recurrence_count' => 'integer',
        'is_installment' => 'boolean',
        'has_chargeback' => 'boolean',
        'is_recurring' => 'boolean',
        'fraud_approved' => 'boolean',
        'authorized_at' => 'datetime',
        'captured_at' => 'datetime',
        'refunded_at' => 'datetime',
        'expected_payment_date' => 'date',
        'chargeback_date' => 'date',
        'fraud_score' => 'decimal:2',
        'items' => 'json',
        'installments_details' => 'json',
        'gateway_request' => 'json',
        'gateway_response' => 'json',
        'antifraud_data' => 'json',
        'attachments' => 'json',
        'metadata' => 'json',
        'custom_fields' => 'json',
    ];

    /**
     * Usuário que realizou a transação
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usuário que criou a transação (operador)
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope para transações aprovadas
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope para transações pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para transações rejeitadas
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope para transações reembolsadas
     */
    public function scopeRefunded($query)
    {
        return $query->whereIn('status', ['refunded', 'partially_refunded']);
    }

    /**
     * Scope para chargebacks
     */
    public function scopeChargeback($query)
    {
        return $query->where('has_chargeback', true);
    }

    /**
     * Scope para transações de hoje
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope para um período específico
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope por bandeira do cartão
     */
    public function scopeByBrand($query, $brand)
    {
        return $query->where('card_brand', $brand);
    }

    /**
     * Scope por gateway de pagamento
     */
    public function scopeByGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Verifica se a transação foi aprovada
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Verifica se pode ser reembolsada
     */
    public function canBeRefunded(): bool
    {
        return in_array($this->status, ['approved', 'captured'])
            && !$this->has_chargeback;
    }

    /**
     * Calcula a taxa efetiva
     */
    public function getEffectiveFeePercentageAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }
        return ($this->fee_amount / $this->amount) * 100;
    }

    /**
     * Valor da parcela (se parcelado)
     */
    public function getInstallmentValueAttribute(): float
    {
        if ($this->installments <= 1) {
            return $this->amount;
        }
        return $this->amount / $this->installments;
    }

    /**
     * Últimos 4 dígitos do cartão mascarados
     */
    public function getMaskedCardNumberAttribute(): string
    {
        return '**** **** **** ' . $this->card_last_digits;
    }

    /**
     * Gera ID único de transação
     */
    public static function generateTransactionId(): string
    {
        $prefix = 'TXN';
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(substr(uniqid(), -6));

        return "{$prefix}-{$timestamp}-{$random}";
    }

    /**
     * Processa reembolso
     */
    public function refund(float $amount, string $reason = null): void
    {
        $isPartial = $amount < $this->amount;

        $this->update([
            'status' => $isPartial ? 'partially_refunded' : 'refunded',
            'refunded_amount' => $amount,
            'refunded_at' => now(),
            'refund_reason' => $reason,
        ]);
    }
}
