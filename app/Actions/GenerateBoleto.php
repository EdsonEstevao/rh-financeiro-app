<?php
// app/Actions/GenerateBoleto.php

namespace App\Actions;

use Illuminate\Support\Facades\{DB, Log};
use Carbon\Carbon;

use App\Models\{Boleto, User};
use App\Services\{AuditService, BillingService};

class GenerateBoleto
{
    /**
     * Create a new action instance.
     */
    public function __construct(
        private readonly BillingService $billingService,
        private readonly AuditService $auditService
    ) {}

    /**
     * Execute the action to generate a single boleto.
     *
     * @param array $data Boleto data
     * @param User|null $createdBy User creating the boleto
     * @return Boleto The created boleto
     * @throws \Exception
     */
    public function execute(array $data, ?User $createdBy = null): Boleto
    {
        // Validar dados obrigatórios
        $this->validateBoletoData($data);

        $creator = $createdBy ?? auth()->user();

        try {
            DB::beginTransaction();

            // Preparar dados do boleto
            $boletoData = $this->prepareBoletoData($data, $creator);

            // Gerar campos bancários
            $boletoData = $this->generateBankingFields($boletoData);

            // Criar o boleto
            $boleto = Boleto::create($boletoData);

            // Registrar auditoria
            $this->auditService->logFinancialAction(
                model: $boleto,
                action: 'boleto_created',
                extraProperties: [
                    'amount' => $boleto->amount,
                    'due_date' => $boleto->due_date->format('d/m/Y'),
                    'payer_name' => $boleto->payer_name,
                    'created_by' => $creator->name,
                ]
            );

            DB::commit();

            Log::info('Boleto generated successfully', [
                'boleto_id' => $boleto->id,
                'boleto_number' => $boleto->boleto_number,
                'amount' => $boleto->amount,
                'user_id' => $boleto->user_id,
                'creator_id' => $creator->id,
            ]);

            return $boleto;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to generate boleto', [
                'error' => $e->getMessage(),
                'data' => $this->sanitizeLogData($data),
                'creator_id' => $creator->id ?? null,
            ]);

            throw $e;
        }
    }

    /**
     * Generate multiple boletos in batch.
     *
     * @param array $boletosData Array of boleto data arrays
     * @param User|null $createdBy User creating the boletos
     * @return array Array of created boletos
     */
    public function executeBatch(array $boletosData, ?User $createdBy = null): array
    {
        $creator = $createdBy ?? auth()->user();
        $createdBoletos = [];
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($boletosData as $index => $data) {
                try {
                    // Validar dados
                    $this->validateBoletoData($data);

                    // Preparar dados
                    $boletoData = $this->prepareBoletoData($data, $creator);
                    $boletoData = $this->generateBankingFields($boletoData);

                    // Criar boleto
                    $boleto = Boleto::create($boletoData);
                    $createdBoletos[] = $boleto;

                } catch (\Exception $e) {
                    $errors[] = [
                        'index' => $index,
                        'error' => $e->getMessage(),
                        'data' => $this->sanitizeLogData($data),
                    ];

                    Log::warning('Failed to generate boleto in batch', [
                        'index' => $index,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Se todos falharam, rollback
            if (count($errors) === count($boletosData)) {
                DB::rollBack();
                throw new \Exception('Todos os boletos falharam na geração em lote.');
            }

            DB::commit();

            // Auditoria do batch
            $this->auditService->log(
                description: "Lote de " . count($createdBoletos) . " boletos gerados com " . count($errors) . " falhas",
                properties: [
                    'created_count' => count($createdBoletos),
                    'failed_count' => count($errors),
                    'errors' => $errors,
                ],
                logName: 'financeiro',
                causer: $creator
            );

            Log::info('Batch boleto generation completed', [
                'created' => count($createdBoletos),
                'failed' => count($errors),
                'creator_id' => $creator->id,
            ]);

            return [
                'success' => true,
                'created' => $createdBoletos,
                'errors' => $errors,
                'total_created' => count($createdBoletos),
                'total_failed' => count($errors),
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Batch boleto generation failed completely', [
                'error' => $e->getMessage(),
                'creator_id' => $creator->id,
            ]);

            throw $e;
        }
    }

    /**
     * Generate recurring boletos.
     *
     * @param array $data Base boleto data
     * @param int $installments Number of installments
     * @param string $frequency Recurrence frequency (weekly, monthly, etc.)
     * @param User|null $createdBy User creating the boletos
     * @return array Created boletos
     */
    public function executeRecurring(
        array $data,
        int $installments,
        string $frequency = 'monthly',
        ?User $createdBy = null
    ): array {
        $creator = $createdBy ?? auth()->user();
        $maxInstallments = config('billing.recurring.max_installments', 36);

        if ($installments < 1 || $installments > $maxInstallments) {
            throw new \InvalidArgumentException(
                "Número de parcelas deve ser entre 1 e {$maxInstallments}."
            );
        }

        $allowedFrequencies = config('billing.recurring.allowed_frequencies', [
            'weekly', 'monthly', 'quarterly', 'yearly'
        ]);

        if (!in_array($frequency, $allowedFrequencies)) {
            throw new \InvalidArgumentException(
                "Frequência inválida. Permitidas: " . implode(', ', $allowedFrequencies)
            );
        }

        try {
            DB::beginTransaction();

            // Gerar primeiro boleto (boleto pai)
            $data['is_recurring'] = true;
            $data['recurrence_rule'] = $frequency;
            $data['recurrence_start'] = $data['due_date'] ?? now()->format('Y-m-d');
            $data['recurrence_count'] = $installments;

            $parentBoleto = $this->execute($data, $creator);

            // Gerar boletos filhos
            $childBoletos = $this->billingService->generateRecurringBoletos(
                parentBoleto: $parentBoleto,
                count: $installments - 1, // -1 porque o pai já é a primeira parcela
                frequency: $frequency
            );

            DB::commit();

            $allBoletos = array_merge([$parentBoleto], $childBoletos);

            Log::info('Recurring boletos generated', [
                'parent_boleto_id' => $parentBoleto->id,
                'total_boletos' => count($allBoletos),
                'frequency' => $frequency,
                'creator_id' => $creator->id,
            ]);

            return [
                'success' => true,
                'parent_boleto' => $parentBoleto,
                'child_boletos' => $childBoletos,
                'all_boletos' => $allBoletos,
                'total' => count($allBoletos),
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to generate recurring boletos', [
                'error' => $e->getMessage(),
                'creator_id' => $creator->id,
            ]);

            throw $e;
        }
    }

    /**
     * Generate boleto from an existing model/order.
     *
     * @param mixed $source Source model (order, invoice, etc.)
     * @param array $options Additional options
     * @return Boleto
     */
    public function executeFromSource(mixed $source, array $options = []): Boleto
    {
        // Mapear dados da fonte para o formato do boleto
        $data = $this->mapSourceToBoletoData($source, $options);

        return $this->execute($data);
    }

    /**
     * Prepare boleto data before creation.
     */
    private function prepareBoletoData(array $data, User $creator): array
    {
        // Carregar dados do usuário se user_id for fornecido
        if (isset($data['user_id']) && !isset($data['payer_name'])) {
            $user = User::find($data['user_id']);
            if ($user) {
                $data['payer_name'] = $data['payer_name'] ?? $user->name;
                $data['payer_document'] = $data['payer_document'] ?? $user->cpf;
                $data['payer_email'] = $data['payer_email'] ?? $user->email;
                $data['payer_phone'] = $data['payer_phone'] ?? $user->phone;
            }
        }

        // Definir datas padrão se não fornecidas
        $data['issue_date'] = $data['issue_date'] ?? now()->format('Y-m-d');

        if (!isset($data['due_date'])) {
            $defaultDays = config('billing.boleto.default_due_days', 30);
            $data['due_date'] = now()->addDays($defaultDays)->format('Y-m-d');
        }

        // Definir valores padrão
        $data['fine_percentage'] = $data['fine_percentage']
            ?? config('billing.boleto.default_fine_percentage', 2.00);

        $data['interest_percentage'] = $data['interest_percentage']
            ?? config('billing.boleto.default_interest_percentage', 1.00);

        // Calcular valor total
        $data['total_amount'] = $this->billingService->calculateTotalAmount(
            baseAmount: $data['amount'],
            discount: $data['discount_amount'] ?? 0,
            finePercentage: $data['fine_percentage'],
            interestPercentage: $data['interest_percentage'],
            dueDate: Carbon::parse($data['due_date'])
        );

        // Adicionar dados do beneficiário
        $data['beneficiary_name'] = $data['beneficiary_name']
            ?? config('billing.boleto.beneficiary_name', config('app.name'));

        $data['beneficiary_document'] = $data['beneficiary_document']
            ?? config('billing.boleto.beneficiary_document');

        // Dados bancários
        $bankConfig = $this->billingService->getConfig();
        $data['bank_code'] = $data['bank_code'] ?? $bankConfig['code'];
        $data['bank_name'] = $data['bank_name'] ?? $bankConfig['name'];
        $data['agency'] = $data['agency'] ?? $bankConfig['agency'];
        $data['account'] = $data['account'] ?? $bankConfig['account'];
        $data['wallet'] = $data['wallet'] ?? $bankConfig['wallet'];
        $data['agreement_number'] = $data['agreement_number'] ?? $bankConfig['agreement_number'];

        // Auditoria
        $data['created_by'] = $creator->id;
        $data['status'] = $data['status'] ?? 'pending';

        // Definir tipo de documento
        if (isset($data['payer_document'])) {
            $docLength = strlen(preg_replace('/[^0-9]/', '', $data['payer_document']));
            $data['payer_document_type'] = $docLength === 14 ? 'cnpj' : 'cpf';
        }

        return $data;
    }

    /**
     * Generate banking fields (barcode, digitable line, our number).
     */
    private function generateBankingFields(array $data): array
    {
        // Gerar número do boleto se não fornecido
        $data['boleto_number'] = $data['boleto_number']
            ?? $this->billingService->generateBoletoNumber();

        // Gerar nosso número
        $data['our_number'] = $data['our_number']
            ?? $this->billingService->generateOurNumber();

        // Gerar código de barras
        $data['barcode'] = $data['barcode']
            ?? $this->billingService->generateBarcode($data);

        // Gerar linha digitável
        $data['digitable_line'] = $data['digitable_line']
            ?? $this->billingService->generateDigitableLine($data['barcode']);

        return $data;
    }

    /**
     * Validate boleto data before generation.
     */
    private function validateBoletoData(array $data): void
    {
        $requiredFields = [
            'amount' => 'Valor do boleto',
            'description' => 'Descrição',
        ];

        foreach ($requiredFields as $field => $label) {
            if (empty($data[$field]) && $data[$field] !== 0) {
                throw new \InvalidArgumentException("Campo obrigatório: {$label}");
            }
        }

        // Validar valor mínimo
        if (isset($data['amount']) && $data['amount'] <= 0) {
            throw new \InvalidArgumentException('O valor do boleto deve ser maior que zero.');
        }

        // Validar datas
        if (isset($data['due_date']) && isset($data['issue_date'])) {
            $dueDate = Carbon::parse($data['due_date']);
            $issueDate = Carbon::parse($data['issue_date']);

            if ($dueDate->lt($issueDate)) {
                throw new \InvalidArgumentException(
                    'A data de vencimento não pode ser anterior à data de emissão.'
                );
            }
        }

        // Validar documento do pagador
        if (isset($data['payer_document']) && !empty($data['payer_document'])) {
            $isValid = $this->billingService->validateDocument($data['payer_document']);

            if (!$isValid) {
                throw new \InvalidArgumentException('CPF/CNPJ do pagador inválido.');
            }
        }

        // Validar email
        if (isset($data['payer_email']) && !empty($data['payer_email'])) {
            if (!filter_var($data['payer_email'], FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Email do pagador inválido.');
            }
        }
    }

    /**
     * Map source data to boleto format.
     */
    private function mapSourceToBoletoData(mixed $source, array $options = []): array
    {
        // Exemplo de mapeamento para diferentes tipos de fonte
        $data = [];

        if ($source instanceof \App\Models\Order) {
            $data = [
                'user_id' => $source->user_id,
                'amount' => $source->total_amount,
                'description' => "Pedido #{$source->id}",
                'reference' => "ORDER-{$source->id}",
                'due_date' => $source->due_date?->format('Y-m-d'),
                'payer_name' => $source->customer_name,
                'payer_document' => $source->customer_document,
                'payer_email' => $source->customer_email,
            ];
        } elseif ($source instanceof \App\Models\Invoice) {
            $data = [
                'user_id' => $source->user_id,
                'amount' => $source->amount,
                'description' => $source->description ?? "Fatura #{$source->id}",
                'reference' => "INV-{$source->id}",
                'due_date' => $source->due_date?->format('Y-m-d'),
            ];
        } else {
            // Tentar extrair dados genéricos
            $data = [
                'amount' => $source->amount ?? $source->total ?? 0,
                'description' => $source->description ?? "Documento #{$source->id}",
                'reference' => $source->reference ?? (string) $source->id,
            ];
        }

        // Merge com opções adicionais
        return array_merge($data, $options);
    }

    /**
     * Sanitize data for logging (remove sensitive information).
     */
    private function sanitizeLogData(array $data): array
    {
        $sensitiveFields = [
            'payer_document', 'card_number', 'cvv', 'password',
            'bank_account', 'routing_number',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Preview boleto data without saving.
     */
    public function preview(array $data): array
    {
        $data = $this->prepareBoletoData($data, auth()->user());
        $data = $this->generateBankingFields($data);

        return [
            'boleto_number' => $data['boleto_number'],
            'our_number' => $data['our_number'],
            'barcode' => $data['barcode'],
            'digitable_line' => $data['digitable_line'],
            'amount' => $data['amount'],
            'total_amount' => $data['total_amount'],
            'due_date' => Carbon::parse($data['due_date'])->format('d/m/Y'),
            'issue_date' => Carbon::parse($data['issue_date'])->format('d/m/Y'),
            'payer_name' => $data['payer_name'] ?? 'N/A',
            'beneficiary_name' => $data['beneficiary_name'] ?? config('app.name'),
            'bank_name' => $data['bank_name'] ?? 'N/A',
            'fine_percentage' => $data['fine_percentage'],
            'interest_percentage' => $data['interest_percentage'],
        ];
    }

    /**
     * Calculate boleto total including all charges.
     */
    public function calculateBoletoTotal(array $data): array
    {
        $amount = (float) ($data['amount'] ?? 0);
        $discount = (float) ($data['discount_amount'] ?? 0);
        $finePercentage = (float) ($data['fine_percentage'] ?? 2.0);
        $interestPercentage = (float) ($data['interest_percentage'] ?? 1.0);
        $dueDate = isset($data['due_date']) ? Carbon::parse($data['due_date']) : null;

        $totalAmount = $this->billingService->calculateTotalAmount(
            baseAmount: $amount,
            discount: $discount,
            finePercentage: $finePercentage,
            interestPercentage: $interestPercentage,
            dueDate: $dueDate
        );

        return [
            'base_amount' => $amount,
            'discount' => $discount,
            'subtotal' => $amount - $discount,
            'fine_percentage' => $finePercentage,
            'interest_percentage' => $interestPercentage,
            'total_amount' => $totalAmount,
            'is_overdue' => $dueDate ? $dueDate->isPast() : false,
        ];
    }

    /**
     * Get available frequencies for recurring boletos.
     */
    public function getAvailableFrequencies(): array
    {
        return [
            'weekly' => 'Semanal',
            'biweekly' => 'Quinzenal',
            'monthly' => 'Mensal',
            'bimonthly' => 'Bimestral',
            'quarterly' => 'Trimestral',
            'semiannual' => 'Semestral',
            'yearly' => 'Anual',
        ];
    }

    /**
     * Get boleto generation statistics for a period.
     */
    public function getGenerationStats(Carbon $startDate, Carbon $endDate): array
    {
        $boletos = Boleto::whereBetween('created_at', [$startDate, $endDate]);

        return [
            'total_generated' => $boletos->count(),
            'total_amount' => $boletos->sum('amount'),
            'by_status' => [
                'pending' => (clone $boletos)->where('status', 'pending')->count(),
                'paid' => (clone $boletos)->where('status', 'paid')->count(),
                'cancelled' => (clone $boletos)->where('status', 'cancelled')->count(),
                'overdue' => (clone $boletos)->where('status', 'overdue')->count(),
            ],
            'by_day' => (clone $boletos)->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
            'average_daily' => $boletos->count() > 0
                ? round($boletos->count() / max($startDate->diffInDays($endDate), 1), 2)
                : 0,
        ];
    }
}