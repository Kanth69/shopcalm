<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailService
{
    private string $apiKey;
    private string $senderEmail;
    private string $senderName;
    private string $apiUrl = 'https://api.brevo.com/v3/smtp/email';

    public function __construct()
    {
        $this->apiKey      = config('services.brevo.api_key', '');
        $this->senderEmail = config('services.brevo.sender_email', '');
        $this->senderName  = config('services.brevo.sender_name', 'WiseKart');
    }

    /**
     * Send an email via Brevo REST API.
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
        if (empty($this->apiKey) || empty($this->senderEmail)) {
            Log::error('[EmailService] Brevo credentials missing — BREVO_API_KEY or BREVO_SENDER_EMAIL not set.');
            return ['success' => false, 'status' => 500];
        }

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
                Log::info('[EmailService] Email sent successfully.', [
                    'to'      => $to,
                    'subject' => $subject,
                    'message_id' => $response->json('messageId') ?? null,
                ]);
                return ['success' => true, 'status' => $response->status()];
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
                'trace'     => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'status' => 500];
        }
    }
}
