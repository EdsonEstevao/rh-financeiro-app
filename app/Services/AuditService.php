<?php
// app/Services/AuditService.php

namespace App\Services;

use Illuminate\Support\Facades\{Auth, Log, Request};
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

use App\Models\User;

class AuditService
{
    /**
     * Log levels
     */
    public const LEVEL_INFO = 'info';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';
    public const LEVEL_CRITICAL = 'critical';

    /**
     * Common log names
     */
    public const LOG_SYSTEM = 'system';
    public const LOG_AUTH = 'auth';
    public const LOG_USER = 'user';
    public const LOG_EMPLOYEE = 'employee';
    public const LOG_FINANCEIRO = 'financeiro';
    public const LOG_RH = 'rh';
    public const LOG_ADMIN = 'admin';
    public const LOG_SECURITY = 'security';

    /**
     * Log a custom activity.
     */
    public function log(
        string $description,
        array $properties = [],
        ?string $logName = self::LOG_SYSTEM,
        ?Model $subject = null,
        ?Authenticatable $causer = null,
        string $level = self::LEVEL_INFO
    ): ?Activity {
        try {
            $activity = activity($logName);

            if ($causer) {
                $activity->causedBy($causer);
            } elseif (Auth::check()) {
                $activity->causedBy(Auth::user());
            }

            if ($subject) {
                $activity->performedOn($subject);
            }

            // Adicionar informações automáticas
            $automaticProperties = $this->getAutomaticProperties();
            $properties = array_merge($automaticProperties, $properties);

            return $activity
                ->withProperties($properties)
                ->log($description);

        } catch (\Exception $e) {
            Log::error('Falha ao registrar atividade', [
                'error' => $e->getMessage(),
                'description' => $description,
            ]);

            return null;
        }
    }

    /**
     * Log user authentication event.
     */
    public function logAuthentication(User $user, string $event, bool $success = true): void
    {
        $description = match ($event) {
            'login' => $success
                ? "Usuário '{$user->name}' realizou login"
                : "Tentativa de login falhou para '{$user->email}'",
            'logout' => "Usuário '{$user->name}' realizou logout",
            'password_reset' => "Usuário '{$user->name}' redefiniu a senha",
            'password_change' => "Usuário '{$user->name}' alterou a senha",
            '2fa_enabled' => "Usuário '{$user->name}' ativou autenticação de dois fatores",
            '2fa_disabled' => "Usuário '{$user->name}' desativou autenticação de dois fatores",
            'account_locked' => "Conta do usuário '{$user->email}' foi bloqueada por múltiplas tentativas",
            'account_unlocked' => "Conta do usuário '{$user->email}' foi desbloqueada",
            default => "Evento de autenticação: {$event} - Usuário '{$user->name}'",
        };

        $this->log(
            description: $description,
            properties: [
                'user_id' => $user->id,
                'email' => $user->email,
                'event' => $event,
                'success' => $success,
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ],
            logName: self::LOG_AUTH,
            causer: $user,
            level: $success ? self::LEVEL_INFO : self::LEVEL_WARNING
        );
    }

    /**
     * Log user management events.
     */
    public function logUserAction(User $targetUser, string $action, ?User $performedBy = null): void
    {
        $performer = $performedBy ?? Auth::user();

        $description = match ($action) {
            'created' => "Usuário '{$targetUser->name}' foi criado por '{$performer->name}'",
            'updated' => "Usuário '{$targetUser->name}' foi atualizado por '{$performer->name}'",
            'deleted' => "Usuário '{$targetUser->name}' foi deletado por '{$performer->name}'",
            'restored' => "Usuário '{$targetUser->name}' foi restaurado por '{$performer->name}'",
            'activated' => "Usuário '{$targetUser->name}' foi ativado por '{$performer->name}'",
            'deactivated' => "Usuário '{$targetUser->name}' foi desativado por '{$performer->name}'",
            'role_changed' => "Perfil do usuário '{$targetUser->name}' foi alterado por '{$performer->name}'",
            'permission_changed' => "Permissões do usuário '{$targetUser->name}' foram alteradas por '{$performer->name}'",
            default => "Ação '{$action}' realizada no usuário '{$targetUser->name}' por '{$performer->name}'",
        };

        $this->log(
            description: $description,
            properties: [
                'target_user_id' => $targetUser->id,
                'target_user_email' => $targetUser->email,
                'target_user_name' => $targetUser->name,
                'action' => $action,
                'performer_id' => $performer->id,
                'performer_name' => $performer->name,
            ],
            logName: self::LOG_USER,
            subject: $targetUser,
            causer: $performer,
            level: in_array($action, ['deleted']) ? self::LEVEL_WARNING : self::LEVEL_INFO
        );
    }

    /**
     * Log employee management events.
     */
    public function logEmployeeAction(Model $employee, string $action): void
    {
        $user = $employee->user ?? null;
        $employeeName = $user?->name ?? 'N/A';

        $description = match ($action) {
            'created' => "Funcionário '{$employeeName}' foi cadastrado",
            'updated' => "Dados do funcionário '{$employeeName}' foram atualizados",
            'deleted' => "Funcionário '{$employeeName}' foi desligado",
            'restored' => "Funcionário '{$employeeName}' foi readmitido",
            'promoted' => "Funcionário '{$employeeName}' foi promovido",
            'salary_changed' => "Salário do funcionário '{$employeeName}' foi alterado",
            'department_changed' => "Departamento do funcionário '{$employeeName}' foi alterado",
            'status_changed' => "Status do funcionário '{$employeeName}' foi alterado para '{$employee->status}'",
            'vacation_started' => "Funcionário '{$employeeName}' entrou em férias",
            'vacation_ended' => "Funcionário '{$employeeName}' retornou de férias",
            'document_added' => "Documento adicionado para o funcionário '{$employeeName}'",
            'document_approved' => "Documento do funcionário '{$employeeName}' foi aprovado",
            'document_rejected' => "Documento do funcionário '{$employeeName}' foi rejeitado",
            default => "Ação '{$action}' no funcionário '{$employeeName}'",
        };

        $this->log(
            description: $description,
            properties: [
                'employee_id' => $employee->id,
                'employee_name' => $employeeName,
                'action' => $action,
                'department' => $employee->department?->name,
                'position' => $employee->position,
            ],
            logName: self::LOG_EMPLOYEE,
            subject: $employee
        );
    }

    /**
     * Log financial events.
     */
    public function logFinancialAction(Model $model, string $action, array $extraProperties = []): void
    {
        $modelName = class_basename($model);
        $identifier = $this->getModelIdentifier($model);

        $description = match ($action) {
            'boleto_created' => "Boleto #{$identifier} foi gerado",
            'boleto_paid' => "Boleto #{$identifier} foi pago",
            'boleto_cancelled' => "Boleto #{$identifier} foi cancelado",
            'boleto_sent' => "Boleto #{$identifier} foi enviado por email",
            'boleto_overdue' => "Boleto #{$identifier} está vencido",
            'transaction_processed' => "Transação #{$identifier} foi processada",
            'transaction_approved' => "Transação #{$identifier} foi aprovada",
            'transaction_rejected' => "Transação #{$identifier} foi rejeitada",
            'transaction_refunded' => "Transação #{$identifier} foi reembolsada",
            'payroll_processed' => "Folha de pagamento foi processada",
            'payroll_paid' => "Pagamento de folha foi confirmado",
            'report_generated' => "Relatório financeiro foi gerado",
            default => "Ação financeira '{$action}' em {$modelName} #{$identifier}",
        };

        $properties = array_merge([
            'model_type' => $modelName,
            'model_id' => $model->id,
            'identifier' => $identifier,
            'action' => $action,
        ], $extraProperties);

        $this->log(
            description: $description,
            properties: $properties,
            logName: self::LOG_FINANCEIRO,
            subject: $model
        );
    }

    /**
     * Log system configuration changes.
     */
    public function logSystemChange(string $setting, $oldValue, $newValue): void
    {
        $description = "Configuração '{$setting}' foi alterada";

        $this->log(
            description: $description,
            properties: [
                'setting' => $setting,
                'old_value' => $this->sanitizeSensitiveData($setting, $oldValue),
                'new_value' => $this->sanitizeSensitiveData($setting, $newValue),
            ],
            logName: self::LOG_SYSTEM,
            level: self::LEVEL_WARNING
        );
    }

    /**
     * Log security events.
     */
    public function logSecurityEvent(string $event, array $properties = []): void
    {
        $description = match ($event) {
            'permission_denied' => 'Tentativa de acesso não autorizado',
            'rate_limit_exceeded' => 'Limite de requisições excedido',
            'suspicious_activity' => 'Atividade suspeita detectada',
            'csrf_token_mismatch' => 'Token CSRF inválido',
            'session_hijack_attempt' => 'Tentativa de sequestro de sessão',
            'file_upload_blocked' => 'Upload de arquivo bloqueado por segurança',
            'sql_injection_attempt' => 'Tentativa de injeção SQL bloqueada',
            'xss_attempt' => 'Tentativa de XSS bloqueada',
            default => "Evento de segurança: {$event}",
        };

        $this->log(
            description: $description,
            properties: array_merge([
                'event' => $event,
                'ip' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'url' => Request::fullUrl(),
                'method' => Request::method(),
            ], $properties),
            logName: self::LOG_SECURITY,
            level: self::LEVEL_CRITICAL
        );
    }

    /**
     * Log model changes (create, update, delete).
     */
    public function logModelChange(Model $model, string $event, array $changes = []): void
    {
        $modelName = class_basename($model);
        $identifier = $this->getModelIdentifier($model);

        $description = match ($event) {
            'created' => "{$modelName} '{$identifier}' foi criado",
            'updated' => "{$modelName} '{$identifier}' foi atualizado",
            'deleted' => "{$modelName} '{$identifier}' foi deletado",
            'restored' => "{$modelName} '{$identifier}' foi restaurado",
            'force_deleted' => "{$modelName} '{$identifier}' foi permanentemente deletado",
            default => "{$modelName} '{$identifier}' - {$event}",
        };

        $this->log(
            description: $description,
            properties: [
                'model' => $modelName,
                'model_id' => $model->id,
                'event' => $event,
                'changes' => $this->sanitizeChanges($changes),
                'changed_attributes' => array_keys($changes),
            ],
            subject: $model
        );
    }

    /**
     * Log data export events.
     */
    public function logExport(string $type, string $format, int $recordCount): void
    {
        $this->log(
            description: "Exportação de {$type} em formato {$format} com {$recordCount} registros",
            properties: [
                'export_type' => $type,
                'format' => $format,
                'record_count' => $recordCount,
            ],
            logName: self::LOG_SYSTEM
        );
    }

    /**
     * Log data import events.
     */
    public function logImport(string $type, int $importedCount, int $failedCount = 0): void
    {
        $this->log(
            description: "Importação de {$type}: {$importedCount} importados, {$failedCount} falhas",
            properties: [
                'import_type' => $type,
                'imported_count' => $importedCount,
                'failed_count' => $failedCount,
            ],
            logName: self::LOG_SYSTEM,
            level: $failedCount > 0 ? self::LEVEL_WARNING : self::LEVEL_INFO
        );
    }

    /**
     * Get recent activities for a specific model.
     */
    public function getModelHistory(Model $model, int $limit = 20): \Illuminate\Support\Collection
    {
        return Activity::where('subject_type', get_class($model))
            ->where('subject_id', $model->id)
            ->with('causer')
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get recent activities for a user.
     */
    public function getUserHistory(User $user, int $limit = 50): \Illuminate\Support\Collection
    {
        return Activity::where('causer_type', User::class)
            ->where('causer_id', $user->id)
            ->with('subject')
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Get activity statistics.
     */
    public function getStatistics(?string $logName = null, ?int $days = 30): array
    {
        $query = Activity::query();

        if ($logName) {
            $query->where('log_name', $logName);
        }

        if ($days) {
            $query->where('created_at', '>=', now()->subDays($days));
        }

        return [
            'total' => $query->count(),
            'by_day' => (clone $query)->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'by_event' => (clone $query)->selectRaw('event, COUNT(*) as count')
                ->groupBy('event')
                ->orderByDesc('count')
                ->get(),
            'by_log_name' => (clone $query)->selectRaw('log_name, COUNT(*) as count')
                ->groupBy('log_name')
                ->orderByDesc('count')
                ->get(),
            'by_causer' => (clone $query)->selectRaw('causer_id, causer_type, COUNT(*) as count')
                ->whereNotNull('causer_id')
                ->groupBy('causer_id', 'causer_type')
                ->with('causer')
                ->orderByDesc('count')
                ->limit(10)
                ->get(),
            'today' => (clone $query)->whereDate('created_at', today())->count(),
            'this_week' => (clone $query)->whereDate('created_at', '>=', now()->startOfWeek())->count(),
            'this_month' => (clone $query)->whereDate('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * Clean old activity logs.
     */
    public function cleanOldLogs(int $daysToKeep = 90): int
    {
        try {
            $cutoffDate = now()->subDays($daysToKeep);

            $count = Activity::where('created_at', '<', $cutoffDate)->count();

            Activity::where('created_at', '<', $cutoffDate)->delete();

            // Log the cleanup action
            $this->log(
                description: "Limpeza de logs antigos: {$count} registros removidos (mais antigos que {$daysToKeep} dias)",
                properties: [
                    'deleted_count' => $count,
                    'cutoff_date' => $cutoffDate->toDateTimeString(),
                    'days_to_keep' => $daysToKeep,
                ],
                logName: self::LOG_SYSTEM,
                level: self::LEVEL_WARNING
            );

            return $count;

        } catch (\Exception $e) {
            Log::error('Falha ao limpar logs de atividade', [
                'error' => $e->getMessage(),
                'days_to_keep' => $daysToKeep,
            ]);

            throw $e;
        }
    }

    /**
     * Get automatic properties for all logs.
     */
    private function getAutomaticProperties(): array
    {
        return [
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
            'timestamp' => now()->toDateTimeString(),
            'timezone' => config('app.timezone'),
            'environment' => app()->environment(),
        ];
    }

    /**
     * Get model identifier for logging.
     */
    private function getModelIdentifier(Model $model): string
    {
        // Tenta obter um identificador amigável do modelo
        $identifiers = ['name', 'title', 'number', 'code', 'id', 'uuid'];

        foreach ($identifiers as $attr) {
            if (isset($model->$attr)) {
                return (string) $model->$attr;
            }
        }

        // Fallback para atributos comuns em modelos específicos
        if ($model instanceof \App\Models\Boleto) {
            return $model->boleto_number ?? (string) $model->id;
        }

        if ($model instanceof \App\Models\CreditCardTransaction) {
            return $model->transaction_id ?? (string) $model->id;
        }

        return (string) ($model->id ?? 'unknown');
    }

    /**
     * Sanitize sensitive data before logging.
     */
    private function sanitizeSensitiveData(string $key, $value): mixed
    {
        $sensitiveKeys = [
            'password', 'password_confirmation', 'secret',
            'api_key', 'token', 'access_token', 'refresh_token',
            'credit_card', 'card_number', 'cvv', 'cvc',
            'bank_account', 'routing_number',
        ];

        foreach ($sensitiveKeys as $sensitiveKey) {
            if (stripos($key, $sensitiveKey) !== false) {
                return '[REDACTED]';
            }
        }

        return $value;
    }

    /**
     * Sanitize changes array for logging.
     */
    private function sanitizeChanges(array $changes): array
    {
        $sanitized = [];

        foreach ($changes as $key => $value) {
            $sanitized[$key] = $this->sanitizeSensitiveData($key, $value);
        }

        return $sanitized;
    }

    /**
     * Get activities for dashboard.
     */
    public function getDashboardActivities(int $limit = 10): \Illuminate\Support\Collection
    {
        return Activity::with('causer')
            ->latest()
            ->take($limit)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'causer_name' => $activity->causer?->name ?? 'Sistema',
                    'causer_avatar' => $activity->causer
                        ? strtoupper(substr($activity->causer->name, 0, 2))
                        : 'SYS',
                    'log_name' => $activity->log_name,
                    'created_at' => $activity->created_at,
                    'created_at_human' => $activity->created_at->diffForHumans(),
                    'properties' => $activity->properties,
                ];
            });
    }

    /**
     * Export activities to array for reports.
     */
    public function exportActivities(array $filters = []): \Illuminate\Support\Collection
    {
        $query = Activity::with('causer', 'subject');

        if (!empty($filters['log_name'])) {
            $query->where('log_name', $filters['log_name']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['causer_id'])) {
            $query->where('causer_id', $filters['causer_id']);
        }

        return $query->latest()->get()->map(function ($activity) {
            return [
                'Data/Hora' => $activity->created_at->format('d/m/Y H:i:s'),
                'Usuário' => $activity->causer?->name ?? 'Sistema',
                'Email' => $activity->causer?->email ?? '',
                'Log' => $activity->log_name,
                'Descrição' => $activity->description,
                'Modelo' => class_basename($activity->subject_type ?? ''),
                'ID Registro' => $activity->subject_id ?? '',
                'IP' => $activity->properties['ip_address'] ?? '',
                'URL' => $activity->properties['url'] ?? '',
            ];
        });
    }
}
