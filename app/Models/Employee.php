<?php
// app/Models/Employee.php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'department_id',
        'supervisor_id',
        'registration_number',
        'position',
        'role',
        'employment_type',
        'work_schedule',
        'workload_hours',
        'salary',
        'salary_type',
        'benefits_value',
        'salary_history',
        'hire_date',
        'termination_date',
        'probation_end_date',
        'last_promotion_date',
        'rg',
        'issuer',
        'birth_date',
        'gender',
        'marital_status',
        'nationality',
        'birth_place',
        'personal_email',
        'phone',
        'mobile',
        'address',
        'city',
        'state',
        'zip_code',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'bank_name',
        'bank_code',
        'agency',
        'account',
        'account_type',
        'pix_key',
        'pis_pasep',
        'ctps',
        'ctps_serie',
        'voter_id',
        'military_id',
        'photo_url',
        'has_dependents',
        'dependents_info',
        'education_level',
        'institution',
        'course',
        'graduation_year',
        'has_health_plan',
        'has_dental_plan',
        'has_life_insurance',
        'has_meal_voucher',
        'has_food_voucher',
        'has_transportation_voucher',
        'has_gym_pass',
        'meal_voucher_value',
        'food_voucher_value',
        'transportation_voucher_value',
        'status',
        'vacation_start_date',
        'vacation_end_date',
        'vacation_days_available',
        'sick_days_available',
        'last_evaluation_date',
        'last_evaluation_score',
        'evaluation_comments',
        'observations',
        'skills',
        'certifications',
        'languages',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'benefits_value' => 'decimal:2',
        'hire_date' => 'date',
        'termination_date' => 'date',
        'probation_end_date' => 'date',
        'last_promotion_date' => 'date',
        'birth_date' => 'date',
        'vacation_start_date' => 'date',
        'vacation_end_date' => 'date',
        'last_evaluation_date' => 'date',
        'has_dependents' => 'boolean',
        'has_health_plan' => 'boolean',
        'has_dental_plan' => 'boolean',
        'has_life_insurance' => 'boolean',
        'has_meal_voucher' => 'boolean',
        'has_food_voucher' => 'boolean',
        'has_transportation_voucher' => 'boolean',
        'has_gym_pass' => 'boolean',
        'workload_hours' => 'integer',
        'vacation_days_available' => 'integer',
        'sick_days_available' => 'integer',
        'last_evaluation_score' => 'decimal:2',
        'salary_history' => 'json',
        'dependents_info' => 'json',
        'skills' => 'json',
        'certifications' => 'json',
        'languages' => 'json',
        'metadata' => 'json',
        'meal_voucher_value' => 'decimal:2',
        'food_voucher_value' => 'decimal:2',
        'transportation_voucher_value' => 'decimal:2',
    ];

    /**
     * Usuário associado ao funcionário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Departamento do funcionário
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Supervisor do funcionário
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'supervisor_id');
    }

    /**
     * Funcionários supervisionados
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'supervisor_id');
    }

    /**
     * Documentos do funcionário
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /**
     * Documentos aprovados
     */
    public function approvedDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class)->where('status', 'approved');
    }

    /**
     * Documentos pendentes
     */
    public function pendingDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class)->where('status', 'pending');
    }

    /**
     * Documentos vencidos
     */
    public function expiredDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class)
            ->where('status', 'expired')
            ->orWhere('expiration_date', '<', now());
    }

    /**
     * Folhas de pagamento do funcionário
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Última folha de pagamento
     */
    public function latestPayroll()
    {
        return $this->hasOne(Payroll::class)->latestOfMany();
    }

    /**
     * Funcionário que criou o registro
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Funcionário que atualizou o registro
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope para funcionários ativos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para funcionários de férias
     */
    public function scopeOnVacation($query)
    {
        return $query->where('status', 'vacation');
    }

    /**
     * Scope para funcionários afastados
     */
    public function scopeOnLeave($query)
    {
        return $query->where('status', 'leave');
    }

    /**
     * Scope para funcionários desligados
     */
    public function scopeTerminated($query)
    {
        return $query->where('status', 'terminated');
    }

    /**
     * Scope para aniversariantes do mês
     */
    public function scopeBirthdayThisMonth($query)
    {
        return $query->whereMonth('birth_date', now()->month);
    }

    /**
     * Scope para funcionários em período de experiência
     */
    public function scopeOnProbation($query)
    {
        return $query->where('probation_end_date', '>=', now())
                    ->where('status', 'active');
    }

    /**
     * Scope para busca por nome ou CPF
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%")
                         ->orWhere('cpf', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('position', 'like', "%{$search}%")
              ->orWhere('registration_number', 'like', "%{$search}%");
        });
    }

    /**
     * Tempo de casa em anos
     */
    public function getYearsOfServiceAttribute(): float
    {
        return $this->hire_date->diffInYears(now());
    }

    /**
     * Nome completo (acessando através do usuário)
     */
    public function getNameAttribute(): string
    {
        return $this->user?->name ?? 'N/A';
    }

    /**
     * Email (acessando através do usuário)
     */
    public function getEmailAttribute(): string
    {
        return $this->user?->email ?? 'N/A';
    }

    /**
     * CPF (acessando através do usuário)
     */
    public function getCpfAttribute(): string
    {
        return $this->user?->cpf ?? 'N/A';
    }

    /**
     * Verifica se o funcionário está ativo
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verifica se está em férias
     */
    public function isOnVacation(): bool
    {
        return $this->status === 'vacation';
    }
}
