<?php
// app/Models/Employee.php

namespace App\Models;

use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

use App\Models\User;
use App\Traits\LogsActivityCustom;

class Employee extends Model
{
    use SoftDeletes, LogsActivityCustom;


    protected $fillable = [
        'user_id', // Relacionamento com User para nome, email, cpf
        'department_id', // Relacionamento com Department
        'supervisor_id', // Relacionamento com outro Employee como supervisor
        'registration_number', // Número de matrícula
        'position', // Cargo
        'role', // Função
        'employment_type', // Tipo de emprego
        'work_schedule', // Escala de trabalho
        'workload_hours', // Carga horária
        'salary', // Salário
        'salary_type', // Tipo de salário
        'benefits_value', // Valor dos benefícios
        'salary_history', // Histórico salarial (JSON)
        'hire_date', // Data de admissão
        'termination_date', // Data de demissão
        'probation_end_date', // Data de término do período de experiência
        'last_promotion_date', // Data da última promoção
        'rg', // Registro Geral
        'issuer', // Órgão emissor
        'birth_date', // Data de nascimento
        'gender', // Gênero
        'marital_status', // Estado civil
        'nationality', // Nacionalidade
        'birth_place', // Local de nascimento
        'personal_email', // E-mail pessoal
        'phone', // Telefone
        'mobile', // Celular
        'address', // Endereço
        'city', // Cidade
        'state', // Estado
        'zip_code', // CEP
        'emergency_contact_name', // Nome do contato de emergência
        'emergency_contact_phone', // Telefone do contato de emergência
        'emergency_contact_relationship', // Relação com o contato de emergência
        'bank_name', // Nome do banco
        'bank_code', // Código do banco
        'agency', // Agência
        'account', // Conta
        'account_type', // Tipo de conta
        'pix_key', // Chave PIX
        'pis_pasep', // PIS/PASEP
        'ctps', // Carteira de Trabalho
        'ctps_serie', // Série da CTPS
        'voter_id', // Título de Eleitor
        'military_id', // Identidade militar
        'photo_url', // URL da foto
        'has_dependents', // Possui dependentes
        'dependents_info', // Informações dos dependentes
        'education_level', // Nível de educação
        'institution', // Instituição
        'course', // Curso
        'graduation_year', // Ano de formação
        'has_health_plan', // Possui plano de saúde
        'has_dental_plan', // Possui plano odontológico
        'has_life_insurance', // Possui seguro de vida
        'has_meal_voucher', // Possui vale refeição
        'has_food_voucher', // Possui vale alimentação
        'has_transportation_voucher', // Possui vale transporte
        'has_gym_pass', // Possui passagem para academia
        'meal_voucher_value', // Valor do vale refeição
        'food_voucher_value', // Valor do vale alimentação
        'transportation_voucher_value', // Valor do vale transporte
        'status', // Status do funcionário (ativo, férias, afastado, desligado)
        'vacation_start_date', // Data de inicio de ferias
        'vacation_end_date', // Data de fim de ferias
        'vacation_days_available', // Dias de férias disponíveis
        'sick_days_available', // Dias de licença médica disponíveis
        'last_evaluation_date', // Data da última avaliação
        'last_evaluation_score', // Nota da última avaliação
        'evaluation_comments', // Comentários da última avaliação
        'observations', // Observações
        'skills', // Habilidades
        'certifications', // Certificação
        'languages', // Idiomas
        'metadata', // Metadados
        'created_by', // Criado por
        'updated_by', // Atualizado por
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
        'deleted_at' => 'datetime',

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
