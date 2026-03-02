<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CashfreeService
{
    protected string $baseUrl;
    protected string $appId;
    protected string $secretKey;
    protected string $apiVersion;

    public function __construct()
    {
        $this->appId = config('cashfree.app_id', '');
        $this->secretKey = config('cashfree.secret_key', '');
        $isProduction = config('cashfree.is_production', false);
        $this->apiVersion = config('cashfree.api_version', '2022-09-01');

        $this->baseUrl = $isProduction
            ? 'https://api.cashfree.com/pg'
            : 'https://sandbox.cashfree.com/pg';
    }

    public function buildOrderPayload(string $orderId, string $customerId, string $customerEmail, string $customerPhone, float $orderAmount, string $orderCurrency): array
    {
        return [
            'order_id' => $orderId,
            'order_amount' => $orderAmount,
            'order_currency' => $orderCurrency,
            'customer_details' => [
                'customer_id' => $customerId,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
            ],
            'order_meta' => [
                'return_url' => url('/api/payment/callback?order_id=' . $orderId),
            ]
        ];
    }

    public function createOrder(array $payload): array
    {
        $response = Http::withHeaders([
            'x-client-id' => $this->appId,
            'x-client-secret' => $this->secretKey,
            'x-api-version' => $this->apiVersion,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/orders', $payload);

        if ($response->successful()) {
            return $response->json();
        }

        // Check if payment channel exists, otherwise use default
        try {
            Log::channel('payment')->error('Cashfree Create Order Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload
            ]);
        } catch (\Exception $e) {
             Log::error('Cashfree Create Order Failed (Payment channel missing)', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload
            ]);
        }

        throw new \Exception('Cashfree Order Creation Failed: ' . $response->body());
    }

    public function getOrder(string $orderId): array
    {
        $response = Http::withHeaders([
            'x-client-id' => $this->appId,
            'x-client-secret' => $this->secretKey,
            'x-api-version' => $this->apiVersion,
        ])->get($this->baseUrl . '/orders/' . $orderId);

        if ($response->successful()) {
            return $response->json();
        }

        try {
            Log::channel('payment')->error('Cashfree Get Order Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $orderId
            ]);
        } catch (\Exception $e) {
            Log::error('Cashfree Get Order Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $orderId
            ]);
        }

        throw new \Exception('Cashfree Get Order Failed: ' . $response->body());
    }

    public function getCheckoutUrl(array $orderResponse): ?string
    {
        // If payment_link is provided directly
        if (!empty($orderResponse['payment_link'])) {
            return $orderResponse['payment_link'];
        }
        
        // If we have a payment_session_id, we might need to construct a URL or return it
        // For this implementation, we'll assume the API returns payment_link or we return null
        return null;
    }
}
