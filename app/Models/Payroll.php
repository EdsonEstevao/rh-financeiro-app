<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'payroll_date', 'gross_salary', 'net_salary', 'deductions', 'bonuses', 'taxes', 'payment_method', 'status', 'notes'])]
class Payroll extends Model
{
    //

     use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'department_id',
        'processed_by',
        'reference_number',
        'period',
        'payment_date',
        'year',
        'month',
        'type',
        'employee_name',
        'employee_cpf',
        'employee_position',
        'employee_department',
        'base_salary',
        'hourly_rate',
        'worked_hours',
        'worked_days',
        'earnings',
        'total_earnings',
        'deductions',
        'total_deductions',
        'benefits',
        'total_benefits',
        'overtime_hours',
        'overtime_amount',
        'night_shift_hours',
        'night_shift_amount',
        'dangerousness_amount',
        'unhealthiness_amount',
        'bonus_amount',
        'commission_amount',
        'commissions_details',
        'advance_amount',
        'loan_amount',
        'fgts_amount',
        'inss_employer_amount',
        'total_charges',
        'taxes',
        'irrf_amount',
        'inss_amount',
        'total_taxes',
        'gross_salary',
        'net_salary',
        'total_cost',
        'vacation_amount',
        'vacation_bonus',
        'thirteenth_amount',
        'thirteenth_installment',
        'termination_date',
        'termination_type',
        'termination_amount',
        'termination_details',
        'status',
        'processed_at',
        'rejection_reason',
        'observations',
        'metadata',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'termination_date' => 'date',
        'processed_at' => 'datetime',
        'base_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'total_benefits' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'night_shift_hours' => 'decimal:2',
        'night_shift_amount' => 'decimal:2',
        'dangerousness_amount' => 'decimal:2',
        'unhealthiness_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'advance_amount' => 'decimal:2',
        'loan_amount' => 'decimal:2',
        'fgts_amount' => 'decimal:2',
        'inss_employer_amount' => 'decimal:2',
        'total_charges' => 'decimal:2',
        'irrf_amount' => 'decimal:2',
        'inss_amount' => 'decimal:2',
        'total_taxes' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'vacation_amount' => 'decimal:2',
        'vacation_bonus' => 'decimal:2',
        'thirteenth_amount' => 'decimal:2',
        'termination_amount' => 'decimal:2',
        'year' => 'integer',
        'month' => 'integer',
        'worked_hours' => 'integer',
        'worked_days' => 'integer',
        'thirteenth_installment' => 'integer',
        'earnings' => 'json',
        'deductions' => 'json',
        'benefits' => 'json',
        'commissions_details' => 'json',
        'taxes' => 'json',
        'termination_details' => 'json',
        'metadata' => 'json',
    ];

    /**
     * Funcionário
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Departamento
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Usuário que processou a folha
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scope para folhas de um período específico
     */
    public function scopeForPeriod($query, $year, $month)
    {
        return $query->where('year', $year)
                    ->where('month', $month);
    }

    /**
     * Scope para folhas processadas
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Scope para folhas pagas
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope para um tipo específico de folha
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Gera número de referência único
     */
    public static function generateReferenceNumber(): string
    {
        $prefix = 'FOL';
        $year = date('Y');
        $month = date('m');
        $random = str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}{$month}-{$random}";
    }

    /**
     * Calcula o custo total para a empresa
     */
    public function calculateTotalCost(): float
    {
        return $this->gross_salary +
               $this->total_charges +
               $this->total_benefits;
    }

}