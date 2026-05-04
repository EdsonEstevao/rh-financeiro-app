<?php
// app/Models/Boleto.php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Carbon\Carbon;

use App\Enums\{BoletoStatus, PaymentMethod};


class Boleto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'created_by',
        'updated_by',
        'boleto_number',
        'our_number',
        'document_number',
        'contract_number',
        'payer_name',
        'payer_document',
        'payer_document_type',
        'payer_email',
        'payer_phone',
        'payer_address',
        'payer_city',
        'payer_state',
        'payer_zip_code',
        'beneficiary_name',
        'beneficiary_document',
        'beneficiary_address',
        'amount',
        'discount_amount',
        'discount_percentage',
        'discount_limit_date',
        'fine_amount',
        'fine_percentage',
        'interest_amount',
        'interest_percentage',
        'other_charges',
        'total_amount',
        'issue_date',
        'due_date',
        'processed_at',
        'paid_at',
        'cancelled_at',
        'bank_code',
        'bank_name',
        'agency',
        'account',
        'wallet',
        'agreement_number',
        'barcode',
        'digitable_line',
        'description',
        'instructions',
        'additional_info',
        'status',
        'status_reason',
        'paid_amount',
        'credit_date',
        'remessa_file',
        'retorno_file',
        'cnab_data',
        'email_sent',
        'email_sent_at',
        'sms_sent',
        'sms_sent_at',
        'days_overdue_notification',
        'is_recurring',
        'recurrence_rule',
        'recurrence_start',
        'recurrence_end',
        'recurrence_count',
        'parent_boleto_id',
        'category',
        'tags',
        'reference',
        'pdf_path',
        'attachments',
        'notes',
        'metadata',
        //novas colunas
        'payment_method'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'fine_amount' => 'decimal:2',
        'fine_percentage' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'interest_percentage' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'discount_limit_date' => 'date',
        'processed_at' => 'date',
        'paid_at' => 'date',
        'cancelled_at' => 'date',
        'credit_date' => 'date',
        'recurrence_start' => 'date',
        'recurrence_end' => 'date',
        'email_sent' => 'boolean',
        'sms_sent' => 'boolean',
        'is_recurring' => 'boolean',
        'email_sent_at' => 'datetime',
        'sms_sent_at' => 'datetime',
        'tags' => 'json',
        'attachments' => 'json',
        'cnab_data' => 'json',
        'metadata' => 'json',
        // Novo campo
        'payment_method' => PaymentMethod::class,
        'status' => BoletoStatus::class,

    ];

    /**
     * Usuário (pagador) do boleto
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Usuário que criou o boleto
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Usuário que atualizou o boleto
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Boleto pai (para recorrência)
     */
    public function parentBoleto(): BelongsTo
    {
        return $this->belongsTo(Boleto::class, 'parent_boleto_id');
    }

    /**
     * Boletos filhos (recorrência)
     */
    public function childBoletos(): HasMany
    {
        return $this->hasMany(Boleto::class, 'parent_boleto_id');
    }

    /**
     * Scope para boletos pendentes
     */
    // public function scopePending($query)
    // {
    //     return $query->where('status', 'pending');
    // }

    /**
     * Scope para boletos pagos
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope para boletos vencidos
     */
    // public function scopeOverdue($query)
    // {
    //     return $query->where('status', 'overdue')
    //                 ->orWhere(function ($q) {
    //                     $q->where('status', 'pending')
    //                       ->where('due_date', '<', now());
    //                 });
    // }

    /**
     * Scope para boletos cancelados
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope para boletos que vencem hoje
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    /**
     * Scope para boletos de um período
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    /**
     * Verifica se o boleto está vencido
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    /**
     * Dias de atraso
     */
    public function getDaysOverdueAttribute(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->due_date->diffInDays(now());
    }

    /**
     * Calcula valor com multa e juros
     */
    public function getTotalWithChargesAttribute(): float
    {
        $total = $this->amount;

        // Aplica desconto se dentro do prazo
        if ($this->discount_limit_date && now()->lte($this->discount_limit_date)) {
            $total -= $this->discount_amount;
        }

        // Aplica multa e juros se vencido
        if ($this->isOverdue()) {
            $total += $this->fine_amount;
            $total += $this->interest_amount * $this->days_overdue;
        }

        return max(0, $total);
    }

    /**
     * Marca boleto como pago
     */
    public function markAsPaid(float $amountPaid, ?Carbon $paidAt = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_amount' => $amountPaid,
            'paid_at' => $paidAt ?? now(),
            'credit_date' => now(),
        ]);
    }

    /**
     * Cancela boleto
     */
    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'status_reason' => $reason,
        ]);
    }

    /**
     * Gera número do boleto
     */
    public static function generateBoletoNumber(): string
    {
        $prefix = 'BOL';
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);

        return "{$prefix}-{$date}-{$random}";
    }

    // Usar o enum para validações
    public function canBeCancelled(): bool
    {
        return $this->status->canBeCancelled();
    }

    // Usar o enum para escopos
    public function scopePending($query)
    {
        return $query->where('status', BoletoStatus::pendingStatuses());
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', BoletoStatus::overdueStatuses());
    }
}