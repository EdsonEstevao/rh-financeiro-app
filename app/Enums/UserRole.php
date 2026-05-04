<?php
// app/Enums/UserRole.php (complementar)

namespace App\Enums;

/**
 * User Role Enum
 *
 * Representa os perfis de usuário disponíveis no sistema.
 */
enum UserRole: string
{
    case ADMIN = 'admin';
    case RH = 'rh';
    case FINANCEIRO = 'financeiro';
    case CONSULTOR = 'consultor';
    case GERENTE = 'gerente';
    case FUNCIONARIO = 'funcionario';

    /**
     * Get the display label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrador',
            self::RH => 'Recursos Humanos',
            self::FINANCEIRO => 'Financeiro',
            self::CONSULTOR => 'Consultor',
            self::GERENTE => 'Gerente',
            self::FUNCIONARIO => 'Funcionário',
        };
    }

    /**
     * Get the color class.
     */
    public function color(): string
    {
        return match ($this) {
            self::ADMIN => 'red',
            self::RH => 'blue',
            self::FINANCEIRO => 'green',
            self::CONSULTOR => 'indigo',
            self::GERENTE => 'purple',
            self::FUNCIONARIO => 'gray',
        };
    }

    /**
     * Get badge CSS classes.
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
     * Check if role has administrative privileges.
     */
    public function isAdministrative(): bool
    {
        return in_array($this, [self::ADMIN, self::RH, self::FINANCEIRO]);
    }

    /**
     * Check if role has management privileges.
     */
    public function isManagement(): bool
    {
        return in_array($this, [self::ADMIN, self::GERENTE]);
    }

    /**
     * Get all as select array.
     */
    public static function toSelectArray(): array
    {
        return array_reduce(self::cases(), fn ($carry, $case) => [
            ...$carry,
            $case->value => $case->label()
        ], []);
    }
}
