<?php
// app/Models/Department.php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Department extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'description',
        'manager_id',
        'parent_id',
        'budget',
        'email',
        'phone',
        'location',
        'capacity',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'budget' => 'decimal:2',
        'is_active' => 'boolean',
        'capacity' => 'integer',
        'metadata' => 'json',
    ];

    /**
     * Gerente do departamento (usuário)
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Departamento pai (auto-relacionamento)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * Sub-departamentos (auto-relacionamento)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    /**
     * Funcionários do departamento
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Contagem de funcionários ativos
     */
    public function activeEmployees(): HasMany
    {
        return $this->hasMany(Employee::class)->where('status', 'active');
    }

    /**
     * Folhas de pagamento do departamento
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Orçamento utilizado (soma dos salários)
     */
    public function getBudgetUsedAttribute(): float
    {
        return $this->employees()
            ->where('status', 'active')
            ->sum('salary');
    }

    /**
     * Orçamento disponível
     */
    public function getBudgetAvailableAttribute(): float
    {
        return $this->budget - $this->budget_used;
    }

    /**
     * Scope para departamentos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para busca por nome ou código
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }
}
