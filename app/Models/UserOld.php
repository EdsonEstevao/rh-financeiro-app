<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\{Casts, Fillable, Hidden};
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};
use Spatie\Permission\Traits\HasRoles;

use App\Models\{Boleto, CreditCardTransaction, Employee};
use Database\Factories\UserFactory;

#[Fillable(['name', 'email', 'password',
// Campos adicionados
        'cpf',
        'rg',
        'phone',
        'mobile',
        'alternative_email',
        'is_active',
        'last_login_at',
        'last_login_ip',
        'locale',
        'timezone',
        'preferences',
        'profile_photo_path',
        'email_notifications',
        'sms_notifications',
        'push_notifications',
        'two_factor_enabled',
        'notes',
])]
#[Hidden(['password', 'remember_token'])]
// #[Casts([
//     'email_verified_at' => 'datetime',
//     'password' => 'hashed',
//     'is_active' => 'boolean',
//     'last_login_at' => 'datetime',
//     'email_notifications' => 'boolean',
//     'sms_notifications' => 'boolean',
//     'push_notifications' => 'boolean',
//     'two_factor_enabled' => 'boolean',
//     'preferences' => 'json',
// ])]
class UserOld extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'preferences' => 'json',
        ];
    }


    /**
     * Relationships
     */
    /**
     * Funcionário associado ao usuário
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Boletos do usuário
     */
    public function boletos(): HasMany
    {
        return $this->hasMany(Boleto::class);
    }

    /**
     * Boletos criados pelo usuário (se for admin/financeiro)
     */
    public function createdBoletos(): HasMany
    {
        return $this->hasMany(Boleto::class, 'created_by');
    }

    /**
     * Transações de cartão de crédito do usuário
     */
    public function creditCardTransactions(): HasMany
    {
        return $this->hasMany(CreditCardTransaction::class);
    }

    /**
     * Transações de cartão criadas pelo usuário
     */
    public function createdCreditCardTransactions(): HasMany
    {
        return $this->hasMany(CreditCardTransaction::class, 'created_by');
    }

    /**
     * Documentos que o usuário fez upload
     */
    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'uploaded_by');
    }

    /**
     * Documentos que o usuário aprovou
     */
    public function approvedDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'approved_by');
    }

    /**
     * Funcionários que o usuário criou
     */
    public function createdEmployees(): HasMany
    {
        return $this->hasMany(Employee::class, 'created_by');
    }

    /**
     * Funcionários que o usuário atualizou
     */
    public function updatedEmployees(): HasMany
    {
        return $this->hasMany(Employee::class, 'updated_by');
    }

    /**
     * Folhas de pagamento processadas pelo usuário
     */
    public function processedPayrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'processed_by');
    }

    /**
     * Departamentos que o usuário gerencia
     */
    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    /**
     * Helper methods para roles
     */
    public function getProfileAttribute(): string
    {
        return $this->roles->first()?->name ?? 'funcionario';
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isRH(): bool
    {
        return $this->hasRole('rh');
    }

    public function isFinanceiro(): bool
    {
        return $this->hasRole('financeiro');
    }

    public function isConsultor(): bool
    {
        return $this->hasRole('consultor');
    }

    public function isGerente(): bool
    {
        return $this->hasRole('gerente');
    }

    public function isFuncionario(): bool
    {
        return $this->hasRole('funcionario');
    }

    /**
     * Scope para usuários ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para busca
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('cpf', 'like', "%{$search}%");
        });
    }
}
