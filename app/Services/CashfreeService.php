<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class CashfreeService
{
    protected string $appId;
    protected string $secretKey;
    protected string $apiVersion;
    protected string $environment;
    protected string $baseUrl;

    public function __construct()
    {
        $this->appId = config('services.cashfree.app_id') ?? env('CASHFREE_APP_ID', '');
        $this->secretKey = config('services.cashfree.secret_key') ?? env('CASHFREE_SECRET_KEY', '');
        $this->apiVersion = config('services.cashfree.api_version') ?? env('CASHFREE_API_VERSION', '2023-08-01');
        $this->environment = strtoupper(config('services.cashfree.environment') ?? env('CASHFREE_ENVIRONMENT', 'TEST'));

        $this->baseUrl = ($this->environment === 'PRODUCTION')
            ? 'https://api.cashfree.com/pg'
            : 'https://sandbox.cashfree.com/pg';
    }

    /**
     * Create a Cashfree Payment Order Session.
     */
    public function createPaymentSession(string $orderNumber, float $amount, array $customerDetails, string $returnUrl): array
    {
        if (empty($this->appId) || empty($this->secretKey)) {
            throw new Exception("Cashfree App ID or Secret Key is not configured in .env");
        }

        $phone = preg_replace('/[^0-9]/', '', $customerDetails['phone'] ?? '9999999999');
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }
        if (strlen($phone) < 10) {
            $phone = '9999999999';
        }

        $payload = [
            'order_id'       => $orderNumber,
            'order_amount'   => round($amount, 2),
            'order_currency' => 'INR',
            'customer_details' => [
                'customer_id'    => (string) ($customerDetails['customer_id'] ?? 'cust_' . time()),
                'customer_name'  => $customerDetails['name'] ?? 'Customer',
                'customer_email' => $customerDetails['email'] ?? 'customer@shopcalm.in',
                'customer_phone' => $phone,
            ],
            'order_meta' => [
                'return_url' => $returnUrl . '?order_id={order_id}',
                'notify_url' => route('checkout.cashfree.webhook'),
            ],
            'order_note' => 'Order #' . $orderNumber . ' on ' . config('app.name'),
        ];

        Log::info("Cashfree: Creating order session for #{$orderNumber}", $payload);

        $response = Http::withHeaders([
            'x-client-id'     => $this->appId,
            'x-client-secret' => $this->secretKey,
            'x-api-version'   => $this->apiVersion,
            'Content-Type'    => 'application/json',
            'Accept'          => 'application/json',
        ])->post("{$this->baseUrl}/orders", $payload);

        if ($response->successful()) {
            $data = $response->json();
            Log::info("Cashfree: Order session created successfully", $data);

            return [
                'success'            => true,
                'payment_session_id' => $data['payment_session_id'] ?? null,
                'cf_order_id'        => $data['cf_order_id'] ?? null,
                'order_status'       => $data['order_status'] ?? 'ACTIVE',
                'environment'        => $this->environment,
            ];
        }

        $errorBody = $response->json();
        Log::error("Cashfree: Order session creation failed", [
            'status' => $response->status(),
            'body'   => $errorBody,
        ]);

        $message = $errorBody['message'] ?? 'Failed to initialize Cashfree payment gateway.';
        throw new Exception($message);
    }

    /**
     * Backward-compatible wrapper for createOrder using Order model.
     */
    public function createOrder(Order $order, User $user, string $returnUrl): array
    {
        return $this->createPaymentSession(
            $order->order_number,
            (float) $order->total_amount,
            [
                'customer_id' => 'cust_' . $user->id,
                'name'        => $order->shipping_name ?? $user->name,
                'email'       => $order->shipping_email ?? $user->email,
                'phone'       => $order->shipping_phone ?? $user->mobile_number ?? '9999999999',
            ],
            $returnUrl
        );
    }

    /**
     * Fetch order details and status from Cashfree.
     */
    public function getOrder(string $orderNumber): array
    {
        $response = Http::withHeaders([
            'x-client-id'     => $this->appId,
            'x-client-secret' => $this->secretKey,
            'x-api-version'   => $this->apiVersion,
            'Accept'          => 'application/json',
        ])->get("{$this->baseUrl}/orders/{$orderNumber}");

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("Cashfree: Fetch order failed for {$orderNumber}", [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);

        return [];
    }

    /**
     * Fetch payment attempts/transactions for an order from Cashfree.
     */
    public function getOrderPayments(string $orderNumber): array
    {
        $response = Http::withHeaders([
            'x-client-id'     => $this->appId,
            'x-client-secret' => $this->secretKey,
            'x-api-version'   => $this->apiVersion,
            'Accept'          => 'application/json',
        ])->get("{$this->baseUrl}/orders/{$orderNumber}/payments");

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Generate dynamic Cashfree Doorstep UPI QR code.
     */
    public function createDoorstepUpiQr(Order $order): array
    {
        $storeName = config('app.name', 'ShopCalm');
        $merchantUpi = config('services.cashfree.upi_vpa', env('CASHFREE_UPI_VPA', 'shopcalm.cashfree@icici'));

        // If Cashfree is configured, generate dynamic UPI payment intent
        try {
            if (!empty($this->appId) && !empty($this->secretKey)) {
                $cfOrder = $this->getOrder($order->order_number);
                if (empty($cfOrder) || ($cfOrder['order_status'] ?? '') === 'EXPIRED') {
                    $user = $order->user ?? new User([
                        'id' => 0,
                        'name' => $order->shipping_name,
                        'email' => $order->shipping_email,
                        'mobile_number' => $order->shipping_phone,
                    ]);
                    $this->createOrder($order, $user, route('checkout.success', $order));
                }

                // Request dynamic QR payment from Cashfree PG
                $response = Http::withHeaders([
                    'x-client-id'     => $this->appId,
                    'x-client-secret' => $this->secretKey,
                    'x-api-version'   => $this->apiVersion,
                    'Content-Type'    => 'application/json',
                    'Accept'          => 'application/json',
                ])->post("{$this->baseUrl}/orders/{$order->order_number}/payments", [
                    'payment_method' => [
                        'upi' => [
                            'channel' => 'qrcode',
                        ],
                    ],
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $qrString = $data['data']['payload']['qrcode']
                             ?? $data['data']['payload']['upi_qr_url']
                             ?? $data['payload']['qrcode']
                             ?? null;

                    if ($qrString) {
                        return [
                            'success'   => true,
                            'gateway'   => 'cashfree_dynamic_qr',
                            'qr_string' => $qrString,
                            'vpa'       => $merchantUpi,
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Cashfree Doorstep UPI QR initialization warning for #{$order->order_number}: " . $e->getMessage());
        }

        // Standard Indian UPI Intent format for Cashfree merchant VPA
        $upiIntent = "upi://pay?pa={$merchantUpi}&pn=" . urlencode($storeName) . "&am={$order->total_amount}&tr={$order->order_number}&cu=INR&tn=" . urlencode("Cashfree Payment for Order #{$order->order_number}");

        return [
            'success'   => true,
            'gateway'   => 'cashfree',
            'qr_string' => $upiIntent,
            'vpa'       => $merchantUpi,
        ];
    }

    /**
     * Verify Cashfree Webhook Signature.
     */
    public function verifyWebhookSignature(string $rawPayload, string $signature, string $timestamp): bool
    {
        if (empty($signature) || empty($timestamp) || empty($this->secretKey)) {
            return false;
        }

        $expectedSignature = base64_encode(hash_hmac('sha256', $timestamp . $rawPayload, $this->secretKey, true));

        return hash_equals($expectedSignature, $signature);
    }
}
