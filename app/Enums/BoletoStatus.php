<?php
// app/Enums/BoletoStatus.php

namespace App\Enums;

/**
 * Boleto Status Enum
 *
 * Representa todos os possíveis status de um boleto bancário
 * no ciclo de vida do sistema.
 */

            // $table->enum('status', [
            //     'draft', 'pending', 'registered', 'paid',
            //     'overdue', 'cancelled', 'protested', 'returned'
            // ])->default('draft');
            // $table->string('status_reason', 255)->nullable();
enum BoletoStatus: string
{
    /**
     * Boleto em rascunho, ainda não finalizado
     */
    case DRAFT = 'draft';

    /**
     * Boleto pendente de pagamento (emitido)
     */
    case PENDING = 'pending';

    /**
     * Boleto registrado no banco
     */
    case REGISTERED = 'registered';

    /**
     * Boleto pago
     */
    case PAID = 'paid';

    /**
     * Boleto vencido (não pago até a data de vencimento)
     */
    case OVERDUE = 'overdue';

    /**
     * Boleto cancelado
     */
    case CANCELLED = 'cancelled';

    /**
     * Boleto protestado
     */
    case PROTESTED = 'protested';

    /**
     * Boleto devolvido pelo banco
     */
    case RETURNED = 'returned';

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the display label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Rascunho',
            self::PENDING => 'Pendente',
            self::REGISTERED => 'Registrado',
            self::PAID => 'Pago',
            self::OVERDUE => 'Vencido',
            self::CANCELLED => 'Cancelado',
            self::PROTESTED => 'Protestado',
            self::RETURNED => 'Devolvido',
        };
    }

    /**
     * Get the badge color class for the status.
     * Compatível com Tailwind CSS.
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PENDING => 'yellow',
            self::REGISTERED => 'blue',
            self::PAID => 'green',
            self::OVERDUE => 'red',
            self::CANCELLED => 'gray',
            self::PROTESTED => 'red',
            self::RETURNED => 'orange',
        };
    }

    /**
     * Get the full Tailwind CSS badge classes.
     */
    public function badgeClasses(): string
    {
        return sprintf(
            'bg-%s-100 text-%s-800 dark:bg-%s-900 dark:text-%s-200',
            $this->color(),
            $this->color(),
            $this->color(),
            $this->color()
        );
    }

    /**
     * Get the icon name for the status.
     */
    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-pencil',
            self::PENDING => 'heroicon-o-clock',
            self::REGISTERED => 'heroicon-o-check-badge',
            self::PAID => 'heroicon-o-check-circle',
            self::OVERDUE => 'heroicon-o-exclamation-triangle',
            self::CANCELLED => 'heroicon-o-x-circle',
            self::PROTESTED => 'heroicon-o-exclamation-circle',
            self::RETURNED => 'heroicon-o-arrow-uturn-left',
        };
    }

    /**
     * Check if the status is considered active/open.
     */
    public function isOpen(): bool
    {
        return in_array($this, [
            self::DRAFT,
            self::PENDING,
            self::REGISTERED,
        ]);
    }

    /**
     * Check if the status is considered closed/finalized.
     */
    public function isClosed(): bool
    {
        return in_array($this, [
            self::PAID,
            self::CANCELLED,
            self::PROTESTED,
            self::RETURNED,
        ]);
    }

    /**
     * Check if the boleto can be edited in this status.
     */
    public function canBeEdited(): bool
    {
        return in_array($this, [
            self::DRAFT,
            self::PENDING,
        ]);
    }

    /**
     * Check if the boleto can be cancelled in this status.
     * Normalmente, apenas boletos que ainda não foram pagos ou protestados podem ser cancelados.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this, [
            self::DRAFT,
            self::PENDING,
            self::REGISTERED,
            self::OVERDUE,
        ]);
    }

    /**
     * Check if the boleto can be marked as paid.
     * Normalmente, apenas boletos que estão pendentes, registrados ou vencidos podem ser pagos.
     */
    public function canBePaid(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::REGISTERED,
            self::OVERDUE,
        ]);
    }

    /**
     * Get allowed transitions from this status.
     * Define quais status são válidos para transição a partir do status atual.
     *
     * @return array<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT => [
                self::PENDING,
                self::CANCELLED,
            ],
            self::PENDING => [
                self::REGISTERED,
                self::PAID,
                self::OVERDUE,
                self::CANCELLED,
            ],
            self::REGISTERED => [
                self::PAID,
                self::OVERDUE,
                self::CANCELLED,
            ],
            self::OVERDUE => [
                self::PAID,
                self::CANCELLED,
                self::PROTESTED,
            ],
            self::PROTESTED => [
                self::PAID,
                self::CANCELLED,
                self::RETURNED,
            ],
            self::PAID, self::CANCELLED, self::RETURNED => [],
        };
    }

    /**
     * Check if transition to another status is allowed.
     * Normalmente, apenas boletos que ainda não foram pagos podem ser cancelados.
     */
    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions(), true);
    }

    /**
     * Get statuses that are considered overdue.
     * Normalmente, apenas o status 'overdue' é considerado vencido, mas dependendo da lógica de negócios, outros status como 'pending' com data de vencimento passada também podem ser considerados vencidos.
     *
     * @return array<self>
     */
    public static function overdueStatuses(): array
    {
        return [self::OVERDUE];
    }

    /**
     * Get statuses that represent pending payment.
     * Normalmente, os status 'draft', 'pending' e 'registered' são considerados pendentes, pois indicam que o boleto ainda não foi pago ou finalizado.
     *
     * @return array<self>
     */
    public static function pendingStatuses(): array
    {
        return [self::DRAFT, self::PENDING, self::REGISTERED];
    }

    /**
     * Get statuses that represent completed payment.
     *
     * @return array<self>
     */
    public static function paidStatuses(): array
    {
        return [self::PAID];
    }

    /**
     * Get statuses that are considered active for reports.
     *
     * @return array<self>
     */
    public static function activeStatuses(): array
    {
        return [self::PENDING, self::REGISTERED, self::OVERDUE, self::PROTESTED];
    }

    /**
     * Get all statuses as array for select inputs.
     *
     * @return array<string, string>
     */
    public static function toSelectArray(): array
    {
        return array_reduce(self::cases(), function ($carry, $case) {
            $carry[$case->value] = $case->label();
            return $carry;
        }, []);
    }

    /**
     * Get statuses available for filtering.
     *
     * @return array<string, string>
     */
    public static function filterableStatuses(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::PAID->value => self::PAID->label(),
            self::OVERDUE->value => self::OVERDUE->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
        ];
    }

    /**
     * Get statistics-friendly grouping.
     *
     * @return array<string, array<self>>
     */
    public static function statGroups(): array
    {
        return [
            'Aberto' => [self::DRAFT, self::PENDING, self::REGISTERED],
            'Vencido' => [self::OVERDUE],
            'Pago' => [self::PAID],
            'Cancelado/Devolvido' => [self::CANCELLED, self::PROTESTED, self::RETURNED],
        ];
    }

    /**
     * Try to create from string value.
     */
    public static function tryFromValue(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return self::tryFrom($value);
    }

    /**
     * Get the severity level for logging.
     */
    public function severityLevel(): string
    {
        return match ($this) {
            self::DRAFT, self::PENDING, self::REGISTERED => 'info',
            self::PAID => 'success',
            self::CANCELLED, self::RETURNED => 'warning',
            self::OVERDUE => 'error',
            self::PROTESTED => 'critical',
        };
    }

    /**
     * Check if status requires immediate action.
     */
    public function requiresAction(): bool
    {
        return in_array($this, [
            self::OVERDUE,
            self::PROTESTED,
            self::RETURNED,
        ]);
    }

    /**
     * Get CSS animation class for status.
     */
    public function animationClass(): string
    {
        return match ($this) {
            self::OVERDUE, self::PROTESTED => 'animate-pulse',
            self::PENDING => 'animate-pulse',
            default => '',
        };
    }

    /**
     * Convert to array for API responses.
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'color' => $this->color(),
            'is_open' => $this->isOpen(),
            'is_closed' => $this->isClosed(),
            'can_edit' => $this->canBeEdited(),
            'can_cancel' => $this->canBeCancelled(),
            'can_pay' => $this->canBePaid(),
            'badge_classes' => $this->badgeClasses(),
        ];
    }

    /**
     * Get all statuses as array of arrays for API.
     *
     * @return array
     */
    public static function toApiArray(): array
    {
        return array_map(fn ($case) => $case->toArray(), self::cases());
    }
}