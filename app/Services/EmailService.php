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
     * @return bool
     */
    public function sendEmail(
        string $to,
        string $subject,
        string $html,
        ?string $text = null
    ): bool {
        if (empty($this->apiKey) || empty($this->senderEmail)) {
            Log::error('[EmailService] Brevo credentials missing — BREVO_API_KEY or BREVO_SENDER_EMAIL not set.');
            return false;
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
            $response = Http::withHeaders([
                'api-key'      => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->post($this->apiUrl, $payload);

            if ($response->successful()) {
                Log::info('[EmailService] Email sent successfully.', [
                    'to'      => $to,
                    'subject' => $subject,
                    'message_id' => $response->json('messageId') ?? null,
                ]);
                return true;
            }

            Log::error('[EmailService] Brevo API returned an error.', [
                'to'      => $to,
                'subject' => $subject,
                'status'  => $response->status(),
                'body'    => $response->body(),
            ]);
            return false;

        } catch (\Throwable $e) {
            Log::error('[EmailService] Brevo API exception.', [
                'to'        => $to,
                'subject'   => $subject,
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
