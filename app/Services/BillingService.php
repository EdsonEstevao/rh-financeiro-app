<?php
// app/Services/BillingService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\{Boleto, User};

class BillingService
{
    /**
     * Bank codes
     */
    public const BANK_BRADESCO = '237';
    public const BANK_ITAU = '341';
    public const BANK_SANTANDER = '033';
    public const BANK_BANCO_DO_BRASIL = '001';
    public const BANK_CAIXA = '104';

    /**
     * Default bank configuration
     */
    private array $bankConfig;

    public function __construct(
        private readonly AuditService $auditService
    ) {
        $this->bankConfig = config('billing.bank', [
            'code' => self::BANK_ITAU,
            'name' => 'Itaú',
            'agency' => '0001',
            'account' => '00000-0',
            'wallet' => '109',
            'agreement_number' => '000000',
        ]);
    }

    /**
     * Generate a unique boleto number.
     */
    public function generateBoletoNumber(): string
    {
        $prefix = 'BOL';
        $date = now()->format('Ymd');
        $random = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $checkDigit = $this->calculateCheckDigit($date . $random);

        return "{$prefix}-{$date}-{$random}-{$checkDigit}";
    }

    /**
     * Generate our number (nosso número).
     */
    public function generateOurNumber(): string
    {
        $year = now()->format('y');
        $sequential = str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        $number = $year . $sequential;
        $checkDigit = $this->modulo11($number);

        return $number . '-' . $checkDigit;
    }

    /**
     * Generate barcode for the boleto.
     */
    public function generateBarcode(array $data = []): string
    {
        // Formato do código de barras (44 dígitos)
        // Posições 1-3: Código do banco
        // Posição 4: Moeda (9 = Real)
        // Posições 5-9: Fator de vencimento
        // Posições 10-19: Valor (10 dígitos)
        // Posições 20-44: Campo livre

        $bankCode = $data['bank_code'] ?? $this->bankConfig['code'];
        $currency = '9'; // Real
        $dueDateFactor = $this->calculateDueDateFactor($data['due_date'] ?? now()->addDays(30));
        $amount = $this->formatAmountForBarcode($data['amount'] ?? 0);

        // Campo livre (25 dígitos)
        $freeField = $this->generateFreeField($data);

        $barcode = $bankCode . $currency . $dueDateFactor . $amount . $freeField;

        return $barcode;
    }

    /**
     * Generate digitable line from barcode.
     */
    public function generateDigitableLine(string $barcode): string
    {
        if (strlen($barcode) !== 44) {
            $barcode = str_pad($barcode, 44, '0');
        }

        // Formatar em 5 grupos separados por espaços
        $groups = [
            substr($barcode, 0, 4) . $this->modulo10(substr($barcode, 0, 4)),
            substr($barcode, 4, 5) . substr($barcode, 9, 5) . '.' . substr($barcode, 14, 5) . $this->modulo10(substr($barcode, 4, 14)),
            substr($barcode, 19, 5) . '.' . substr($barcode, 24, 5) . '.' . substr($barcode, 29, 5) . '.' . substr($barcode, 34, 3) . $this->modulo10(substr($barcode, 19, 17)),
            substr($barcode, 37, 1),
        ];

        // Último grupo: fator vencimento + valor
        $groups[] = substr($barcode, 5, 4) . substr($barcode, 9, 10);

        return implode(' ', $groups);
    }

    /**
     * Calculate total amount including fees and interest.
     */
    public function calculateTotalAmount(
        float $baseAmount,
        float $discount = 0,
        float $finePercentage = 2.0,
        float $interestPercentage = 1.0,
        ?Carbon $dueDate = null
    ): float {
        $total = $baseAmount;

        // Aplicar desconto
        if ($discount > 0) {
            $total -= $discount;
        }

        // Se estiver vencido, aplicar multa e juros
        if ($dueDate && $dueDate->isPast()) {
            $daysOverdue = $dueDate->diffInDays(now());

            // Multa (valor fixo ou percentual)
            $fine = $total * ($finePercentage / 100);
            $total += $fine;

            // Juros por dia de atraso
            $interest = $total * ($interestPercentage / 100) * ($daysOverdue / 30);
            $total += $interest;
        }

        return round($total, 2);
    }

    /**
     * Calculate late payment charges.
     */
    public function calculateLateCharges(Boleto $boleto): array
    {
        if (!$boleto->isOverdue()) {
            return [
                'days_overdue' => 0,
                'fine' => 0,
                'interest' => 0,
                'total_charges' => 0,
                'total_amount' => $boleto->amount,
            ];
        }

        $daysOverdue = $boleto->days_overdue;
        $baseAmount = $boleto->amount;

        // Multa
        $finePercentage = $boleto->fine_percentage ?? 2.0;
        $fine = $baseAmount * ($finePercentage / 100);

        // Juros
        $interestPercentage = $boleto->interest_percentage ?? 1.0;
        $interest = $baseAmount * ($interestPercentage / 100) * ($daysOverdue / 30);

        $totalCharges = $fine + $interest;
        $totalAmount = $baseAmount + $totalCharges;

        return [
            'days_overdue' => $daysOverdue,
            'base_amount' => $baseAmount,
            'fine_percentage' => $finePercentage,
            'fine' => round($fine, 2),
            'interest_percentage' => $interestPercentage,
            'interest' => round($interest, 2),
            'total_charges' => round($totalCharges, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Process boleto payment.
     */
    public function processPayment(Boleto $boleto, float $amountPaid, Carbon $paymentDate): bool
    {
        try {
            $lateCharges = $this->calculateLateCharges($boleto);
            $totalDue = $lateCharges['total_amount'];

            // Verificar se o valor pago cobre o total
            if ($amountPaid < $totalDue) {
                Log::warning('Pagamento parcial de boleto', [
                    'boleto_id' => $boleto->id,
                    'amount_paid' => $amountPaid,
                    'total_due' => $totalDue,
                ]);

                // Pode implementar pagamento parcial se necessário
            }

            $boleto->markAsPaid(
                amountPaid: $amountPaid,
                paidAt: $paymentDate
            );

            // Registrar na auditoria
            $this->auditService->logFinancialAction(
                model: $boleto,
                action: 'boleto_paid',
                extraProperties: [
                    'amount_paid' => $amountPaid,
                    'total_due' => $totalDue,
                    'late_charges' => $lateCharges,
                    'payment_date' => $paymentDate->format('d/m/Y'),
                ]
            );

            Log::info('Boleto payment processed', [
                'boleto_id' => $boleto->id,
                'amount_paid' => $amountPaid,
                'payment_date' => $paymentDate->format('Y-m-d'),
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to process boleto payment', [
                'error' => $e->getMessage(),
                'boleto_id' => $boleto->id,
                'amount_paid' => $amountPaid,
            ]);

            throw $e;
        }
    }

    /**
     * Cancel a boleto.
     */
    public function cancelBoleto(Boleto $boleto, string $reason, ?User $cancelledBy = null): bool
    {
        if (in_array($boleto->status, ['paid', 'cancelled'])) {
            throw new \Exception('Apenas boletos pendentes podem ser cancelados.');
        }

        try {
            $boleto->cancel($reason);

            // Cancelar boletos filhos (recorrência)
            $boleto->childBoletos()
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->each(function ($childBoleto) use ($reason) {
                    $childBoleto->cancel("Cancelado devido ao cancelamento do boleto pai: {$reason}");
                });

            // Auditoria
            $this->auditService->logFinancialAction(
                model: $boleto,
                action: 'boleto_cancelled',
                extraProperties: [
                    'reason' => $reason,
                    'cancelled_by' => $cancelledBy?->name ?? 'Sistema',
                ]
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to cancel boleto', [
                'error' => $e->getMessage(),
                'boleto_id' => $boleto->id,
            ]);

            throw $e;
        }
    }

    /**
     * Generate recurring boletos.
     */
    public function generateRecurringBoletos(Boleto $parentBoleto, int $count, string $frequency = 'monthly'): array
    {
        $boletos = [];
        $currentDate = Carbon::parse($parentBoleto->due_date);

        for ($i = 1; $i <= $count; $i++) {
            $nextDate = match ($frequency) {
                'weekly' => $currentDate->addWeek(),
                'biweekly' => $currentDate->addWeeks(2),
                'monthly' => $currentDate->addMonth(),
                'bimonthly' => $currentDate->addMonths(2),
                'quarterly' => $currentDate->addMonths(3),
                'semiannual' => $currentDate->addMonths(6),
                'yearly' => $currentDate->addYear(),
                default => $currentDate->addMonth(),
            };

            $boletoData = $parentBoleto->replicate()->toArray();

            // Remover campos que não devem ser copiados
            unset(
                $boletoData['id'],
                $boletoData['boleto_number'],
                $boletoData['our_number'],
                $boletoData['barcode'],
                $boletoData['digitable_line'],
                $boletoData['created_at'],
                $boletoData['updated_at'],
                $boletoData['paid_at'],
                $boletoData['processed_at'],
                $boletoData['cancelled_at']
            );

            $boletoData['due_date'] = $nextDate->format('Y-m-d');
            $boletoData['issue_date'] = now()->format('Y-m-d');
            $boletoData['status'] = 'pending';
            $boletoData['parent_boleto_id'] = $parentBoleto->id;
            $boletoData['boleto_number'] = $this->generateBoletoNumber();
            $boletoData['our_number'] = $this->generateOurNumber();
            $boletoData['barcode'] = $this->generateBarcode($boletoData);
            $boletoData['digitable_line'] = $this->generateDigitableLine($boletoData['barcode']);
            $boletoData['recurrence_count'] = $i;

            $boleto = Boleto::create($boletoData);
            $boletos[] = $boleto;

            $currentDate = $nextDate;
        }

        Log::info('Recurring boletos generated', [
            'parent_boleto_id' => $parentBoleto->id,
            'generated_count' => count($boletos),
            'frequency' => $frequency,
        ]);

        return $boletos;
    }

    /**
     * Get boletos summary for reports.
     */
    public function getSummary(?Carbon $startDate = null, ?Carbon $endDate = null, ?int $userId = null): array
    {
        $query = Boleto::query();

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $boletos = $query->get();

        return [
            'total_count' => $boletos->count(),
            'total_amount' => $boletos->sum('amount'),
            'paid_count' => $boletos->where('status', 'paid')->count(),
            'paid_amount' => $boletos->where('status', 'paid')->sum('amount'),
            'pending_count' => $boletos->where('status', 'pending')->count(),
            'pending_amount' => $boletos->where('status', 'pending')->sum('amount'),
            'overdue_count' => $boletos->filter(fn($b) => $b->isOverdue())->count(),
            'overdue_amount' => $boletos->filter(fn($b) => $b->isOverdue())->sum('amount'),
            'cancelled_count' => $boletos->where('status', 'cancelled')->count(),
            'cancelled_amount' => $boletos->where('status', 'cancelled')->sum('amount'),
            'average_amount' => $boletos->count() > 0 ? $boletos->avg('amount') : 0,
            'by_category' => $boletos->groupBy('category')->map(fn($group) => [
                'count' => $group->count(),
                'total' => $group->sum('amount'),
            ]),
            'by_status' => [
                'paid' => $boletos->where('status', 'paid')->count(),
                'pending' => $boletos->where('status', 'pending')->count(),
                'overdue' => $boletos->filter(fn($b) => $b->isOverdue())->count(),
                'cancelled' => $boletos->where('status', 'cancelled')->count(),
            ],
        ];
    }

    /**
     * Get aging report (contas a receber por idade).
     */
    public function getAgingReport(?int $userId = null): array
    {
        $query = Boleto::whereIn('status', ['pending', 'overdue'])
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                  ->where('due_date', '<', now());
            });

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $boletos = $query->get();

        $aging = [
            'to_win' => ['count' => 0, 'amount' => 0],       // A vencer
            'overdue_1_30' => ['count' => 0, 'amount' => 0],   // 1-30 dias
            'overdue_31_60' => ['count' => 0, 'amount' => 0],  // 31-60 dias
            'overdue_61_90' => ['count' => 0, 'amount' => 0],  // 61-90 dias
            'overdue_91_180' => ['count' => 0, 'amount' => 0], // 91-180 dias
            'overdue_180_plus' => ['count' => 0, 'amount' => 0], // 180+ dias
        ];

        foreach ($boletos as $boleto) {
            if ($boleto->due_date->isFuture()) {
                $aging['to_win']['count']++;
                $aging['to_win']['amount'] += $boleto->amount;
            } else {
                $daysOverdue = $boleto->due_date->diffInDays(now());

                match (true) {
                    $daysOverdue <= 30 => $key = 'overdue_1_30',
                    $daysOverdue <= 60 => $key = 'overdue_31_60',
                    $daysOverdue <= 90 => $key = 'overdue_61_90',
                    $daysOverdue <= 180 => $key = 'overdue_91_180',
                    default => $key = 'overdue_180_plus',
                };

                $aging[$key]['count']++;
                $aging[$key]['amount'] += $boleto->amount;
            }
        }

        // Calcular percentuais
        $totalAmount = $boletos->sum('amount');
        foreach ($aging as &$group) {
            $group['percentage'] = $totalAmount > 0
                ? round(($group['amount'] / $totalAmount) * 100, 2)
                : 0;
        }

        return $aging;
    }

    /**
     * Get daily financial summary.
     */
    public function getDailySummary(Carbon $date): array
    {
        return [
            'date' => $date->format('Y-m-d'),
            'issued_count' => Boleto::whereDate('issue_date', $date)->count(),
            'issued_amount' => Boleto::whereDate('issue_date', $date)->sum('amount'),
            'due_count' => Boleto::whereDate('due_date', $date)->count(),
            'due_amount' => Boleto::whereDate('due_date', $date)->sum('amount'),
            'paid_count' => Boleto::whereDate('paid_at', $date)->count(),
            'paid_amount' => Boleto::whereDate('paid_at', $date)->sum('paid_amount'),
            'cancelled_count' => Boleto::whereDate('cancelled_at', $date)->count(),
        ];
    }

    /**
     * Calculate due date factor.
     * Fator de vencimento = dias desde 07/10/1997
     */
    private function calculateDueDateFactor(string|Carbon $dueDate): string
    {
        $baseDate = Carbon::parse('1997-10-07');
        $dueDate = Carbon::parse($dueDate);

        $days = $baseDate->diffInDays($dueDate);

        return str_pad($days, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Format amount for barcode (10 dígitos, últimos 2 são centavos).
     */
    private function formatAmountForBarcode(float $amount): string
    {
        return str_pad(
            number_format($amount, 2, '', ''),
            10,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generate free field (campo livre) for barcode.
     */
    private function generateFreeField(array $data): string
    {
        // Campo livre = 25 dígitos
        // Estrutura varia por banco
        // Exemplo genérico:
        $parts = [
            $data['wallet'] ?? $this->bankConfig['wallet'],           // Carteira (3 dígitos)
            $data['our_number'] ?? $this->generateOurNumber(),         // Nosso número (9 dígitos)
            $data['agency'] ?? $this->bankConfig['agency'],           // Agência (4 dígitos)
            $data['account'] ?? $this->bankConfig['account'],         // Conta (7 dígitos)
            '0',                                                       // DAC (1 dígito)
            '0',                                                       // Filler (1 dígito)
        ];

        $freeField = implode('', $parts);
        return str_pad(substr($freeField, 0, 25), 25, '0');
    }

    /**
     * Modulo 10 check digit calculation.
     */
    private function modulo10(string $number): int
    {
        $sum = 0;
        $multiplier = 2;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $product = (int) $number[$i] * $multiplier;
            $sum += $product > 9 ? $product - 9 : $product;
            $multiplier = $multiplier === 2 ? 1 : 2;
        }

        $remainder = $sum % 10;
        return $remainder === 0 ? 0 : 10 - $remainder;
    }

    /**
     * Modulo 11 check digit calculation.
     */
    private function modulo11(string $number): int
    {
        $sum = 0;
        $multiplier = 2;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $sum += (int) $number[$i] * $multiplier;
            $multiplier = $multiplier >= 9 ? 2 : $multiplier + 1;
        }

        $remainder = $sum % 11;

        if ($remainder === 0 || $remainder === 1) {
            return 0;
        }

        return 11 - $remainder;
    }

    /**
     * Calculate check digit for boleto number.
     */
    private function calculateCheckDigit(string $number): string
    {
        return (string) $this->modulo10($number);
    }

    /**
     * Validate CPF/CNPJ.
     */
    public function validateDocument(string $document): bool
    {
        $document = preg_replace('/[^0-9]/', '', $document);

        if (strlen($document) === 11) {
            return $this->validateCPF($document);
        }

        if (strlen($document) === 14) {
            return $this->validateCNPJ($document);
        }

        return false;
    }

    /**
     * Validate CPF.
     */
    private function validateCPF(string $cpf): bool
    {
        // Eliminar CPFs inválidos conhecidos
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Validar dígitos
        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += $cpf[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ($cpf[$t] != $digit) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate CNPJ.
     */
    private function validateCNPJ(string $cnpj): bool
    {
        // Validar dígitos
        $length = strlen($cnpj) - 2;
        $numbers = substr($cnpj, 0, $length);
        $digits = substr($cnpj, $length);
        $sum = 0;
        $pos = $length - 7;

        for ($i = $length; $i >= 1; $i--) {
            $sum += $numbers[$length - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }

        $result = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);
        if ($result != $digits[0]) {
            return false;
        }

        $length++;
        $numbers = substr($cnpj, 0, $length);
        $sum = 0;
        $pos = $length - 7;

        for ($i = $length; $i >= 1; $i--) {
            $sum += $numbers[$length - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }

        $result = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        return $result == $digits[1];
    }

    /**
     * Format money value for display.
     */
    public function formatMoney(float $amount): string
    {
        return 'R$ ' . number_format($amount, 2, ',', '.');
    }

    /**
     * Parse money string to float.
     */
    public function parseMoney(string $amount): float
    {
        $amount = str_replace(['R$', ' ', '.'], '', $amount);
        $amount = str_replace(',', '.', $amount);

        return (float) $amount;
    }

    /**
     * Get billing configuration.
     */
    public function getConfig(): array
    {
        return $this->bankConfig;
    }

    /**
     * Update billing configuration.
     */
    public function updateConfig(array $config): void
    {
        $this->bankConfig = array_merge($this->bankConfig, $config);
    }
}
