<?php
// app/Services/PaymentGateway.php

namespace App\Services;

use Illuminate\Support\Facades\{Http, Log};
use Illuminate\Http\Client\RequestException;
use Exception;

use App\Models\CreditCardTransaction;

class PaymentGateway
{
    private string $apiKey;
    private string $apiSecret;
    private string $apiUrl;
    private string $environment;
    private array $config;

    public function __construct()
    {
        // Carregar configurações com valores padrão
        $this->config = config('services.payment_gateway', []);

        $this->apiKey = $this->config['api_key'] ?? '';
        $this->apiSecret = $this->config['api_secret'] ?? '';
        $this->apiUrl = $this->config['api_url'] ?? 'https://api.payment-gateway.com/v1';
        $this->environment = $this->config['environment'] ?? 'sandbox';

        // Validar configurações apenas se não estiver em ambiente de teste
        if (app()->environment('production') && empty($this->apiKey)) {
            Log::warning('Payment Gateway API key is not configured');
        }
    }

    /**
     * Process credit card payment.
     */
    public function processCreditCard(array $data): CreditCardTransaction
    {
        $this->validateCreditCardData($data);

        try {
            // Em ambiente de desenvolvimento/sandbox, simular processamento
            if ($this->environment === 'sandbox' || app()->environment('local')) {
                return $this->simulateCreditCardProcessing($data);
            }

            // Tokenização do cartão (PCI Compliance)
            $tokenizedCard = $this->tokenizeCard($data['card']);

            // Processar pagamento no gateway real
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/transactions", [
                'amount' => (int) ($data['amount'] * 100), // Converter para centavos
                'currency' => 'BRL',
                'card_token' => $tokenizedCard,
                'installments' => $data['installments'] ?? 1,
                'description' => $data['description'] ?? '',
                'customer' => [
                    'name' => $data['customer_name'] ?? '',
                    'email' => $data['customer_email'] ?? '',
                    'document' => $this->cleanDocument($data['customer_document'] ?? ''),
                ],
                'metadata' => [
                    'order_id' => $data['order_id'] ?? null,
                    'user_id' => $data['user_id'] ?? null,
                ],
            ]);

            if ($response->successful()) {
                return $this->saveSuccessfulTransaction($response->json());
            }

            // Tratar resposta de erro do gateway
            throw new Exception(
                $response->json('message') ?? 'Erro ao processar pagamento no gateway',
                $response->status()
            );

        } catch (RequestException $e) {
            Log::error('Payment gateway request failed', [
                'error' => $e->getMessage(),
                'data' => $this->sanitizeData($data),
            ]);

            throw new Exception('Falha na comunicação com o gateway de pagamento: ' . $e->getMessage());
        } catch (Exception $e) {
            Log::error('Credit card processing failed', [
                'error' => $e->getMessage(),
                'data' => $this->sanitizeData($data),
            ]);

            throw $e;
        }
    }

    /**
     * Process boleto payment.
     */
    public function generateBoleto(array $data): array
    {
        try {
            // Em ambiente sandbox, gerar boleto simulado
            if ($this->environment === 'sandbox' || app()->environment('local')) {
                return $this->simulateBoletoGeneration($data);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/boletos", [
                'amount' => (int) ($data['amount'] * 100),
                'due_date' => $data['due_date'],
                'description' => $data['description'] ?? '',
                'payer' => [
                    'name' => $data['payer_name'] ?? '',
                    'document' => $this->cleanDocument($data['payer_document'] ?? ''),
                    'email' => $data['payer_email'] ?? '',
                ],
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Erro ao gerar boleto no gateway');

        } catch (Exception $e) {
            Log::error('Boleto generation failed', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process PIX payment.
     */
    public function generatePix(array $data): array
    {
        try {
            if ($this->environment === 'sandbox' || app()->environment('local')) {
                return $this->simulatePixGeneration($data);
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/pix", [
                'amount' => (int) ($data['amount'] * 100),
                'expiration_minutes' => $data['expiration_minutes'] ?? 30,
                'description' => $data['description'] ?? '',
                'payer_name' => $data['payer_name'] ?? '',
                'payer_document' => $this->cleanDocument($data['payer_document'] ?? ''),
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            throw new Exception('Erro ao gerar PIX no gateway');

        } catch (Exception $e) {
            Log::error('PIX generation failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Process refund.
     */
    public function processRefund(string $transactionId, float $amount): bool
    {
        try {
            if ($this->environment === 'sandbox' || app()->environment('local')) {
                return true;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->apiUrl}/transactions/{$transactionId}/refund", [
                'amount' => (int) ($amount * 100),
            ]);

            return $response->successful();

        } catch (Exception $e) {
            Log::error('Refund processing failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);
            throw $e;
        }
    }

    /**
     * Tokenize credit card data.
     */
    private function tokenizeCard(array $cardData): string
    {
        // Em produção, enviar dados do cartão para o gateway e receber um token
        // Nunca armazenar dados completos do cartão (PCI Compliance)

        if ($this->environment === 'sandbox' || app()->environment('local')) {
            return 'tok_sandbox_' . md5(uniqid());
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->apiUrl}/tokens", [
            'card_number' => $cardData['number'],
            'holder_name' => $cardData['holder_name'],
            'expiration_month' => $cardData['expiration_month'],
            'expiration_year' => $cardData['expiration_year'],
            'cvv' => $cardData['cvv'],
        ]);

        if ($response->successful()) {
            return $response->json('token');
        }

        throw new Exception('Falha na tokenização do cartão');
    }

    /**
     * Save successful transaction.
     */
    private function saveSuccessfulTransaction(array $response): CreditCardTransaction
    {
        return CreditCardTransaction::create([
            'transaction_id' => $response['id'] ?? CreditCardTransaction::generateTransactionId(),
            'gateway_transaction_id' => $response['gateway_id'] ?? null,
            'authorization_code' => $response['authorization_code'] ?? null,
            'amount' => ($response['amount'] ?? 0) / 100,
            'original_amount' => ($response['original_amount'] ?? $response['amount'] ?? 0) / 100,
            'fee_amount' => ($response['fee'] ?? 0) / 100,
            'net_amount' => ($response['net_amount'] ?? $response['amount'] ?? 0) / 100,
            'installments' => $response['installments'] ?? 1,
            'status' => $response['status'] ?? 'approved',
            'gateway_status' => $response['status'] ?? 'approved',
            'gateway_response' => $response,
            'gateway' => $this->environment,
            'processed_at' => now(),
        ]);
    }

    /**
     * Simulate credit card processing for sandbox.
     */
    private function simulateCreditCardProcessing(array $data): CreditCardTransaction
    {
        // Simular processamento (aprovar 90% das transações)
        $approved = rand(1, 100) <= 90;

        $transaction = new CreditCardTransaction();
        $transaction->transaction_id = CreditCardTransaction::generateTransactionId();
        $transaction->gateway_transaction_id = 'sandbox_' . uniqid();
        $transaction->authorization_code = strtoupper(substr(md5(uniqid()), 0, 10));
        $transaction->amount = $data['amount'];
        $transaction->original_amount = $data['amount'];
        $transaction->fee_amount = $data['amount'] * 0.03;
        $transaction->net_amount = $data['amount'] * 0.97;
        $transaction->installments = $data['installments'] ?? 1;
        $transaction->card_holder_name = $data['card']['holder_name'] ?? '';
        $transaction->card_last_digits = substr($data['card']['number'] ?? '', -4);
        $transaction->card_brand = $this->detectCardBrand($data['card']['number'] ?? '');
        $transaction->card_type = 'credit';
        $transaction->customer_name = $data['customer_name'] ?? '';
        $transaction->customer_email = $data['customer_email'] ?? '';
        $transaction->customer_document = $data['customer_document'] ?? '';
        $transaction->description = $data['description'] ?? '';
        $transaction->status = $approved ? 'approved' : 'rejected';
        $transaction->gateway_status = $approved ? 'approved' : 'rejected';
        $transaction->gateway = 'sandbox';
        $transaction->authorized_at = $approved ? now() : null;

        if (!$approved) {
            $transaction->status_reason = 'Transação recusada pela simulação';
        }

        return $transaction;
    }

    /**
     * Simulate boleto generation for sandbox.
     */
    private function simulateBoletoGeneration(array $data): array
    {
        $boletoNumber = 'BOL-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        return [
            'id' => uniqid('boleto_'),
            'boleto_number' => $boletoNumber,
            'barcode' => '34191.79001 01000.' . rand(100000, 999999) . ' 00000.' . rand(100000, 999999) . ' 1 ' . rand(1000000000, 9999999999),
            'digitable_line' => '34191.79001 01000.' . rand(100000, 999999) . ' 00000.' . rand(100000, 999999) . ' 1 ' . rand(1000000000, 9999999999),
            'our_number' => rand(10000000, 99999999),
            'amount' => $data['amount'],
            'due_date' => $data['due_date'],
            'status' => 'pending',
            'pdf_url' => url('/boletos/' . $boletoNumber . '.pdf'),
        ];
    }

    /**
     * Simulate PIX generation for sandbox.
     */
    private function simulatePixGeneration(array $data): array
    {
        return [
            'id' => uniqid('pix_'),
            'qrcode' => '00020126580014br.gov.bcb.pix0136' . uniqid() . '5204000053039865405' . number_format($data['amount'], 2, '', '') . '5802BR5925' . config('app.name') . '6009SAOPAULO62070503***6304' . strtoupper(dechex(rand(1000, 9999))),
            'qrcode_image' => url('/pix/qrcode/' . uniqid() . '.png'),
            'copy_paste' => '00020126580014br.gov.bcb.pix0136' . uniqid() . '5204000053039865405' . number_format($data['amount'], 2, '', ''),
            'expiration' => now()->addMinutes($data['expiration_minutes'] ?? 30)->toIso8601String(),
            'amount' => $data['amount'],
            'status' => 'pending',
        ];
    }

    /**
     * Validate credit card data.
     */
    private function validateCreditCardData(array $data): void
    {
        if (empty($data['amount']) || $data['amount'] <= 0) {
            throw new Exception('Valor da transação inválido');
        }

        if (empty($data['card']['number'])) {
            throw new Exception('Número do cartão é obrigatório');
        }

        if (empty($data['customer_name'])) {
            throw new Exception('Nome do cliente é obrigatório');
        }
    }

    /**
     * Detect credit card brand by number.
     */
    private function detectCardBrand(string $number): string
    {
        $number = preg_replace('/\D/', '', $number);

        return match (true) {
            strlen($number) >= 1 && $number[0] == '4' => 'visa',
            strlen($number) >= 2 && $number[0] == '5' && $number[1] >= '1' && $number[1] <= '5' => 'mastercard',
            strlen($number) >= 2 && $number[0] == '3' && ($number[1] == '4' || $number[1] == '7') => 'amex',
            strlen($number) >= 6 && in_array(substr($number, 0, 6), ['401179', '401178', '431274', '438935', '451416', '457393', '457631', '457632', '504175', '627780', '636297', '636368']) => 'elo',
            strlen($number) >= 2 && $number[0] == '3' && ($number[1] == '0' || $number[1] == '6' || $number[1] == '8') => 'diners',
            strlen($number) >= 2 && $number[0] == '6' => 'discover',
            default => 'other',
        };
    }

    /**
     * Clean document number.
     */
    private function cleanDocument(string $document): string
    {
        return preg_replace('/[^0-9]/', '', $document);
    }

    /**
     * Sanitize data for logging (remove sensitive info).
     */
    private function sanitizeData(array $data): array
    {
        if (isset($data['card'])) {
            $data['card'] = [
                'last_digits' => substr($data['card']['number'] ?? '', -4),
                'brand' => $this->detectCardBrand($data['card']['number'] ?? ''),
            ];
        }

        if (isset($data['customer_document'])) {
            $data['customer_document'] = substr($data['customer_document'], 0, 3) . '.***.***-**';
        }

        return $data;
    }

    /**
     * Check if gateway is configured.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiUrl);
    }

    /**
     * Get gateway status.
     */
    public function getStatus(): array
    {
        return [
            'configured' => $this->isConfigured(),
            'environment' => $this->environment,
            'api_url' => $this->apiUrl,
            'masked_api_key' => $this->maskApiKey(),
        ];
    }

    /**
     * Mask API key for display.
     */
    private function maskApiKey(): string
    {
        if (empty($this->apiKey)) {
            return 'Not configured';
        }

        $length = strlen($this->apiKey);
        if ($length <= 8) {
            return str_repeat('*', $length);
        }

        return substr($this->apiKey, 0, 4) . str_repeat('*', $length - 8) . substr($this->apiKey, -4);
    }
}
