<?php
// app/Services/PaymentOldGateway.php

namespace App\Services;

use Illuminate\Support\Facades\{Http, Log};

use App\Models\CreditCardTransaction;

class PaymentOLdGateway
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.payment_gateway.api_key');
        // $this->apiUrl = config('services.payment_gateway.url');
    }

    public function processCreditCard(array $data): CreditCardTransaction
    {
        try {
            // Tokenização do cartão (PCI Compliance)
            $tokenizedCard = $this->tokenizeCard($data['card']);

            // Processar pagamento
            $response = Http::withToken($this->apiKey)
                ->post("{$this->apiUrl}/transactions", [
                    'amount' => $data['amount'] * 100, // Centavos
                    'card_token' => $tokenizedCard,
                    'installments' => $data['installments'] ?? 1,
                    'description' => $data['description'],
                    'customer' => [
                        'name' => $data['customer_name'],
                        'email' => $data['customer_email'],
                        'document' => $data['customer_document'],
                    ]
                ]);

            if ($response->successful()) {
                return $this->saveTransaction($response->json());
            }

            throw new \Exception($response->json('message', 'Erro ao processar pagamento'));

        } catch (\Exception $e) {
            Log::error('Payment failed', [
                'error' => $e->getMessage(),
                'data' => array_except($data, ['card.cvv', 'card.number'])
            ]);

            throw $e;
        }
    }

    private function tokenizeCard(array $cardData): string
    {
        // Implementar tokenização do cartão
        // Retornar token seguro
        return 'token_' . encrypt($cardData['number']);
    }

    private function saveTransaction(array $response): CreditCardTransaction
    {
        return CreditCardTransaction::create([
            'gateway_id' => $response['id'],
            'status' => $response['status'],
            'amount' => $response['amount'] / 100,
            'installments' => $response['installments'],
            'gateway_response' => $response,
            'processed_at' => now(),
        ]);
    }
}
