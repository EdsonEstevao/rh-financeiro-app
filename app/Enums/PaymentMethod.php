<?php
// app/Enums/PaymentMethod.php

namespace App\Enums;

/**
 * Payment Method Enum
 *
 * Representa todos os métodos de pagamento disponíveis no sistema.
 */
enum PaymentMethod: string
{
    /**
     * Boleto bancário
     */
    case BOLETO = 'boleto';

    /**
     * Cartão de crédito
     */
    case CREDIT_CARD = 'credit_card';

    /**
     * Cartão de débito
     */
    case DEBIT_CARD = 'debit_card';

    /**
     * PIX (Pagamento Instantâneo)
     */
    case PIX = 'pix';

    /**
     * Transferência bancária (TED/DOC)
     */
    case BANK_TRANSFER = 'bank_transfer';

    /**
     * Dinheiro
     */
    case CASH = 'cash';

    /**
     * Cheque
     */
    case CHECK = 'check';

    /**
     * Débito em conta
     */
    case DIRECT_DEBIT = 'direct_debit';

    /**
     * Carteira digital (PicPay, Mercado Pago, etc.)
     */
    case DIGITAL_WALLET = 'digital_wallet';

    /**
     * Convênio/Contrato
     */
    case AGREEMENT = 'agreement';

    /**
     * Depósito bancário
     */
    case DEPOSIT = 'deposit';

    /**
     * Vale/Cupom
     */
    case VOUCHER = 'voucher';

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Get the display label for the payment method.
     */
    public function label(): string
    {
        return match ($this) {
            self::BOLETO => 'Boleto Bancário',
            self::CREDIT_CARD => 'Cartão de Crédito',
            self::DEBIT_CARD => 'Cartão de Débito',
            self::PIX => 'PIX',
            self::BANK_TRANSFER => 'Transferência Bancária',
            self::CASH => 'Dinheiro',
            self::CHECK => 'Cheque',
            self::DIRECT_DEBIT => 'Débito em Conta',
            self::DIGITAL_WALLET => 'Carteira Digital',
            self::AGREEMENT => 'Convênio',
            self::DEPOSIT => 'Depósito Bancário',
            self::VOUCHER => 'Vale/Cupom',
        };
    }

    /**
     * Get short label for the payment method.
     */
    public function shortLabel(): string
    {
        return match ($this) {
            self::BOLETO => 'Boleto',
            self::CREDIT_CARD => 'Crédito',
            self::DEBIT_CARD => 'Débito',
            self::PIX => 'PIX',
            self::BANK_TRANSFER => 'TED/DOC',
            self::CASH => 'Dinheiro',
            self::CHECK => 'Cheque',
            self::DIRECT_DEBIT => 'Débito',
            self::DIGITAL_WALLET => 'Wallet',
            self::AGREEMENT => 'Convênio',
            self::DEPOSIT => 'Depósito',
            self::VOUCHER => 'Vale',
        };
    }

    /**
     * Get the icon name for the payment method.
     */
    public function icon(): string
    {
        return match ($this) {
            self::BOLETO => 'heroicon-o-document-text',
            self::CREDIT_CARD => 'heroicon-o-credit-card',
            self::DEBIT_CARD => 'heroicon-o-banknotes',
            self::PIX => 'heroicon-o-bolt',
            self::BANK_TRANSFER => 'heroicon-o-building-library',
            self::CASH => 'heroicon-o-currency-dollar',
            self::CHECK => 'heroicon-o-document-check',
            self::DIRECT_DEBIT => 'heroicon-o-arrow-down-circle',
            self::DIGITAL_WALLET => 'heroicon-o-device-phone-mobile',
            self::AGREEMENT => 'heroicon-o-clipboard-document',
            self::DEPOSIT => 'heroicon-o-arrow-trending-up',
            self::VOUCHER => 'heroicon-o-ticket',
        };
    }

    /**
     * Get the color class for the payment method.
     */
    public function color(): string
    {
        return match ($this) {
            self::BOLETO => 'blue',
            self::CREDIT_CARD => 'purple',
            self::DEBIT_CARD => 'indigo',
            self::PIX => 'green',
            self::BANK_TRANSFER => 'orange',
            self::CASH => 'emerald',
            self::CHECK => 'amber',
            self::DIRECT_DEBIT => 'cyan',
            self::DIGITAL_WALLET => 'pink',
            self::AGREEMENT => 'teal',
            self::DEPOSIT => 'lime',
            self::VOUCHER => 'rose',
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
     * Check if the payment method is electronic.
     */
    public function isElectronic(): bool
    {
        return in_array($this, [
            self::CREDIT_CARD,
            self::DEBIT_CARD,
            self::PIX,
            self::BANK_TRANSFER,
            self::DIGITAL_WALLET,
            self::DIRECT_DEBIT,
        ]);
    }

    /**
     * Check if the payment method is physical/manual.
     */
    public function isPhysical(): bool
    {
        return in_array($this, [
            self::CASH,
            self::CHECK,
            self::VOUCHER,
        ]);
    }

    /**
     * Check if the payment method requires bank processing.
     */
    public function requiresBankProcessing(): bool
    {
        return in_array($this, [
            self::BOLETO,
            self::BANK_TRANSFER,
            self::DIRECT_DEBIT,
            self::DEPOSIT,
        ]);
    }

    /**
     * Check if the payment method is instant.
     */
    public function isInstant(): bool
    {
        return in_array($this, [
            self::PIX,
            self::CASH,
        ]);
    }

    /**
     * Get the typical processing time in days.
     */
    public function processingDays(): int
    {
        return match ($this) {
            self::PIX, self::CASH, self::DIGITAL_WALLET => 0,
            self::DEBIT_CARD => 1,
            self::CREDIT_CARD => 30, // Média para recebimento da operadora
            self::BOLETO => 2,
            self::BANK_TRANSFER => 1,
            self::DIRECT_DEBIT => 1,
            self::CHECK => 3,
            self::DEPOSIT => 1,
            self::AGREEMENT => 30,
            self::VOUCHER => 0,
        };
    }

    /**
     * Get the typical fee percentage.
     */
    public function typicalFee(): float
    {
        return match ($this) {
            self::CREDIT_CARD => 3.0,
            self::DEBIT_CARD => 1.5,
            self::PIX => 0.0,
            self::BOLETO => 2.0,
            self::DIGITAL_WALLET => 2.5,
            default => 0.0,
        };
    }

    /**
     * Check if payment method accepts installments.
     */
    public function acceptsInstallments(): bool
    {
        return match ($this) {
            self::CREDIT_CARD => true,
            self::AGREEMENT => true,
            default => false,
        };
    }

    /**
     * Get maximum installments for this method.
     */
    public function maxInstallments(): int
    {
        return match ($this) {
            self::CREDIT_CARD => 12,
            self::AGREEMENT => 36,
            default => 1,
        };
    }

    /**
     * Check if payment method requires customer document.
     */
    public function requiresDocument(): bool
    {
        return in_array($this, [
            self::BOLETO,
            self::CREDIT_CARD,
            self::BANK_TRANSFER,
            self::AGREEMENT,
        ]);
    }

    /**
     * Check if payment method is available for given amount.
     */
    public function isAvailableForAmount(float $amount): bool
    {
        return match ($this) {
            self::CREDIT_CARD => $amount >= 5.00,
            self::BOLETO => $amount >= 10.00,
            self::PIX => $amount >= 0.01,
            self::BANK_TRANSFER => $amount >= 1.00,
            default => true,
        };
    }

    /**
     * Get payment methods that allow refund.
     *
     * @return array<self>
     */
    public static function refundableMethods(): array
    {
        return [
            self::CREDIT_CARD,
            self::DEBIT_CARD,
            self::PIX,
            self::DIGITAL_WALLET,
        ];
    }

    /**
     * Get payment methods available for recurring payments.
     *
     * @return array<self>
     */
    public static function recurringMethods(): array
    {
        return [
            self::BOLETO,
            self::CREDIT_CARD,
            self::DIRECT_DEBIT,
            self::AGREEMENT,
        ];
    }

    /**
     * Get commonly used payment methods.
     *
     * @return array<self>
     */
    public static function commonMethods(): array
    {
        return [
            self::PIX,
            self::CREDIT_CARD,
            self::BOLETO,
            self::DEBIT_CARD,
        ];
    }

    /**
     * Get all methods as array for select inputs.
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
     * Get methods grouped by category.
     *
     * @return array<string, array<string, string>>
     */
    public static function groupedForSelect(): array
    {
        return [
            'Pagamento Instantâneo' => [
                self::PIX->value => self::PIX->label(),
                self::CASH->value => self::CASH->label(),
                self::DIGITAL_WALLET->value => self::DIGITAL_WALLET->label(),
            ],
            'Cartões' => [
                self::CREDIT_CARD->value => self::CREDIT_CARD->label(),
                self::DEBIT_CARD->value => self::DEBIT_CARD->label(),
            ],
            'Boletos e Transferências' => [
                self::BOLETO->value => self::BOLETO->label(),
                self::BANK_TRANSFER->value => self::BANK_TRANSFER->label(),
                self::DEPOSIT->value => self::DEPOSIT->label(),
                self::DIRECT_DEBIT->value => self::DIRECT_DEBIT->label(),
            ],
            'Outros' => [
                self::CHECK->value => self::CHECK->label(),
                self::AGREEMENT->value => self::AGREEMENT->label(),
                self::VOUCHER->value => self::VOUCHER->label(),
            ],
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
     * Get payment methods for API responses.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'short_label' => $this->shortLabel(),
            'color' => $this->color(),
            'icon' => $this->icon(),
            'is_electronic' => $this->isElectronic(),
            'is_instant' => $this->isInstant(),
            'processing_days' => $this->processingDays(),
            'typical_fee' => $this->typicalFee(),
            'accepts_installments' => $this->acceptsInstallments(),
            'max_installments' => $this->maxInstallments(),
            'badge_classes' => $this->badgeClasses(),
        ];
    }

    /**
     * Get all methods as array of arrays for API.
     *
     * @return array
     */
    public static function toApiArray(): array
    {
        return array_map(fn ($case) => $case->toArray(), self::cases());
    }

    /**
     * Get payment methods statistics.
     *
     * @return array
     */
    public static function getStatistics(): array
    {
        return [
            'total_methods' => count(self::cases()),
            'electronic' => count(array_filter(self::cases(), fn ($m) => $m->isElectronic())),
            'physical' => count(array_filter(self::cases(), fn ($m) => $m->isPhysical())),
            'instant' => count(array_filter(self::cases(), fn ($m) => $m->isInstant())),
            'recurring' => count(array_filter(self::cases(), fn ($m) => in_array($m, self::recurringMethods()))),
        ];
    }

    /**
     * Filter payment methods by criteria.
     *
     * @return array<self>
     */
    public static function filterBy(
        ?bool $electronic = null,
        ?bool $instant = null,
        ?bool $recurring = null,
        ?float $minAmount = null
    ): array {
        return array_filter(self::cases(), function ($method) use ($electronic, $instant, $recurring, $minAmount) {
            if ($electronic !== null && $method->isElectronic() !== $electronic) {
                return false;
            }
            if ($instant !== null && $method->isInstant() !== $instant) {
                return false;
            }
            if ($recurring !== null && !in_array($method, self::recurringMethods())) {
                return false;
            }
            if ($minAmount !== null && !$method->isAvailableForAmount($minAmount)) {
                return false;
            }
            return true;
        });
    }
}
