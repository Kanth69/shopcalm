<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    private string $apiKey;
    private string $senderEmail;
    private string $senderName;
    private string $apiUrl = 'https://api.brevo.com/v3/smtp/email';

    public function __construct()
    {
        $this->apiKey      = config('services.brevo.api_key', env('BREVO_API_KEY', ''));
        $this->senderEmail = config('services.brevo.sender_email') ?: config('mail.from.address', 'support@shopcalm.in');
        $this->senderName  = config('services.brevo.sender_name') ?: config('app.name', 'ShopCalm');
    }

    /**
     * Send an email via Brevo REST API with automatic SMTP Fallback.
     *
     * @param  string       $to      Recipient email address
     * @param  string       $subject Email subject line
     * @param  string       $html    HTML body
     * @param  string|null  $text    Plain-text fallback (optional)
     * @return array
     */
    public function sendEmail(
        string $to,
        string $subject,
        string $html,
        ?string $text = null
    ): array {
        // Attempt 1: Send via Brevo REST API
        if (!empty($this->apiKey)) {
            $brevoResult = $this->sendViaBrevoApi($to, $subject, $html, $text);
            if ($brevoResult['success']) {
                return $brevoResult;
            }
            Log::warning("[EmailService] Brevo API failed (Status: {$brevoResult['status']}). Attempting automatic fallback to SMTP Mailer...");
        } else {
            Log::info('[EmailService] Brevo API Key not set. Falling back to SMTP Mailer...');
        }

        // Attempt 2: Automatic Fallback to SMTP / Laravel Mail Transport
        return $this->sendViaSmtp($to, $subject, $html, $text);
    }

    /**
     * Send email via Brevo HTTP API.
     */
    private function sendViaBrevoApi(
        string $to,
        string $subject,
        string $html,
        ?string $text = null
    ): array {
        $payload = [
            'sender' => [
                'name'  => $this->senderName,
                'email' => $this->senderEmail,
            ],
            'to' => [
                ['email' => $to],
            ],
            'subject'     => $subject,
            'htmlContent' => $html,
        ];

        if ($text) {
            $payload['textContent'] = $text;
        }

        try {
            $request = Http::withHeaders([
                'api-key'      => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ]);
            
            // Bypass SSL verification in local development (fixes cURL error 60 on Windows)
            if (app()->environment('local')) {
                $request = $request->withoutVerifying();
            }

            $response = $request->post($this->apiUrl, $payload);

            if ($response->successful()) {
                Log::info('[EmailService] Email sent successfully via Brevo API.', [
                    'to'         => $to,
                    'subject'    => $subject,
                    'message_id' => $response->json('messageId') ?? null,
                ]);
                return ['success' => true, 'status' => $response->status(), 'driver' => 'brevo'];
            }

            Log::error('[EmailService] Brevo API returned an error.', [
                'to'      => $to,
                'subject' => $subject,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
            return ['success' => false, 'status' => $response->status()];

        } catch (\Throwable $e) {
            Log::error('[EmailService] Brevo API exception.', [
                'to'        => $to,
                'subject'   => $subject,
                'exception' => $e->getMessage(),
            ]);
            return ['success' => false, 'status' => 500];
        }
    }

    /**
     * Fallback: Send email via Laravel SMTP Mailer.
     */
    private function sendViaSmtp(
        string $to,
        string $subject,
        string $html,
        ?string $text = null
    ): array {
        try {
            $fromEmail = $this->senderEmail ?: config('mail.from.address', 'support@shopcalm.in');
            $fromName  = $this->senderName ?: config('mail.from.name', 'ShopCalm');

            // Use configured default mailer (smtp or custom)
            $mailer = config('mail.default', 'smtp');

            Mail::mailer($mailer)->html($html, function ($message) use ($to, $subject, $fromEmail, $fromName) {
                $message->to($to)
                        ->from($fromEmail, $fromName)
                        ->subject($subject);
            });

            Log::info("[EmailService] Email sent successfully via Fallback Mailer ({$mailer}).", [
                'to'      => $to,
                'subject' => $subject,
                'driver'  => $mailer,
            ]);

            return ['success' => true, 'status' => 200, 'driver' => $mailer];

        } catch (\Throwable $e) {
            Log::error('[EmailService] SMTP Fallback Mailer also failed.', [
                'to'        => $to,
                'subject'   => $subject,
                'exception' => $e->getMessage(),
            ]);

            return ['success' => false, 'status' => 500, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send HTML Order Confirmation & GST Tax Invoice Email to customer (if email is provided).
     */
    public function sendOrderConfirmation(\App\Models\Order $order): bool
    {
        $recipientEmail = $order->shipping_email;

        if (empty($recipientEmail) && $order->user) {
            $recipientEmail = $order->user->email;
        }

        if (empty($recipientEmail) || !filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
            Log::info("[EmailService] Order #{$order->order_number} has no valid customer email. Skipping email invoice.");
            return false;
        }

        try {
            $order->load(['items.product', 'user']);
            $storeName = \App\Models\Setting::get('store_name', 'ShopCalm');
            $subject = "Order Confirmed: #{$order->order_number} - {$storeName}";

            $html = view('emails.orders.confirmation', [
                'order' => $order,
                'storeName' => $storeName,
            ])->render();

            $result = $this->sendEmail($recipientEmail, $subject, $html);
            return $result['success'] ?? false;
        } catch (\Throwable $e) {
            Log::error("[EmailService] Failed to generate/send order confirmation email for #{$order->order_number}: " . $e->getMessage());
            return false;
        }
    }
}
