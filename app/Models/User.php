<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{HasMany, HasOne};
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
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

    /**
     * Funcionário associado ao usuário.
     */
    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Boletos do usuário.
     */
    public function boletos(): HasMany
    {
        return $this->hasMany(Boleto::class);
    }

    /**
     * Transações de cartão de crédito do usuário.
     */
    public function creditCardTransactions(): HasMany
    {
        return $this->hasMany(CreditCardTransaction::class);
    }

    /**
     * Atividades realizadas pelo usuário (Spatie Activity Log).
     */
    public function actions(): HasMany
    {
        return $this->hasMany(Activity::class, 'causer_id')
            ->where('causer_type', self::class);
    }

    /**
     * Atividades onde o usuário foi o alvo (subject).
     */
    public function activitiesAsSubject(): HasMany
    {
        return $this->hasMany(Activity::class, 'subject_id')
            ->where('subject_type', self::class);
    }

    /**
     * Helper methods para roles.
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
     * Scope para usuários ativos.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para busca.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('cpf', 'like', "%{$search}%");
        });
    }
}