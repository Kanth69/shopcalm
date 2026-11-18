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
        $this->apiKey      = \App\Models\Setting::get('brevo_api_key') ?: (config('services.brevo.api_key') ?: env('BREVO_API_KEY', ''));
        $this->senderEmail = \App\Models\Setting::get('brevo_sender_email') ?: (config('services.brevo.sender_email') ?: config('mail.from.address', 'support@shopcalm.in'));
        $this->senderName  = \App\Models\Setting::get('brevo_sender_name') ?: (config('services.brevo.sender_name') ?: config('app.name', 'ShopCalm'));
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
            $request = Http::timeout(5)->withHeaders([
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

    /**
     * Fetch real-time Brevo Account Quota, Plan, and Remaining Email Credits.
     */
    public function getAccountQuota(): array
    {
        if (empty($this->apiKey)) {
            return [
                'connected'     => false,
                'message'       => 'Brevo REST API Key is not configured in Store Settings.',
                'credits'       => 0,
                'credits_label' => '0 (Key Missing)',
                'plan_type'     => 'Not Configured',
                'account_email' => 'N/A',
                'company_name'  => 'N/A',
                'smtp_enabled'  => false,
            ];
        }

        try {
            $request = Http::withHeaders([
                'api-key' => $this->apiKey,
                'Accept'  => 'application/json',
            ]);

            if (app()->environment('local')) {
                $request = $request->withoutVerifying();
            }

            $response = $request->get('https://api.brevo.com/v3/account');

            if ($response->successful()) {
                $data = $response->json();

                $email = $data['email'] ?? 'N/A';
                $company = $data['companyName'] ?? ($data['firstName'] ?? 'Brevo Account');
                $plans = $data['plan'] ?? [];

                $credits = 0;
                $planType = 'Free';
                $creditsType = 'daily';

                if (!empty($plans) && is_array($plans)) {
                    foreach ($plans as $p) {
                        if (isset($p['credits'])) {
                            $credits += (int) $p['credits'];
                        }
                        if (isset($p['type'])) {
                            $planType = ucfirst($p['type']);
                        }
                        if (isset($p['creditsType'])) {
                            $creditsType = $p['creditsType'];
                        }
                    }
                }

                $smtpEnabled = (bool) ($data['relay']['enabled'] ?? true);

                return [
                    'connected'     => true,
                    'account_email' => $email,
                    'company_name'  => $company,
                    'plan_type'     => $planType,
                    'credits'       => $credits,
                    'credits_label' => number_format($credits) . ($creditsType === 'sendLimit' ? ' / day' : ' credits'),
                    'credits_type'  => $creditsType,
                    'smtp_enabled'  => $smtpEnabled,
                    'sender_email'  => $this->senderEmail,
                    'sender_name'   => $this->senderName,
                ];
            }

            $errorMsg = $response->json('message') ?? ('HTTP ' . $response->status());
            return [
                'connected'     => false,
                'message'       => "Brevo API Error: {$errorMsg}",
                'credits'       => 0,
                'credits_label' => 'Error',
                'plan_type'     => 'API Key Invalid',
                'account_email' => 'N/A',
                'company_name'  => 'N/A',
                'smtp_enabled'  => false,
            ];

        } catch (\Throwable $e) {
            return [
                'connected'     => false,
                'message'       => 'Connection exception: ' . $e->getMessage(),
                'credits'       => 0,
                'credits_label' => 'Error',
                'plan_type'     => 'Offline',
                'account_email' => 'N/A',
                'company_name'  => 'N/A',
                'smtp_enabled'  => false,
            ];
        }
    }

    /**
     * Send a Live Test Email to verify Brevo API connectivity.
     */
    public function sendTestEmail(string $recipientEmail): array
    {
        $storeName = \App\Models\Setting::get('store_name', 'ShopCalm');
        $subject = "{$storeName} - Brevo REST Email Service Verification";
        $html = "
            <div style='font-family: Arial, sans-serif; padding: 24px; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px;'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <h2 style='color: #4f46e5; margin: 0;'>{$storeName}</h2>
                    <p style='color: #64748b; font-size: 14px; margin-top: 4px;'>Transactional Email Delivery Test</p>
                </div>
                <div style='background-color: #f8fafc; padding: 16px; border-radius: 8px; margin-bottom: 20px;'>
                    <p style='margin: 0 0 8px 0; color: #1e293b; font-weight: bold;'>✓ Brevo Email Service Connected Successfully!</p>
                    <p style='margin: 0; color: #475569; font-size: 14px;'>This automated verification email confirms that your store's transactional email delivery via Brevo REST API is live and operational.</p>
                </div>
                <div style='font-size: 13px; color: #64748b; border-top: 1px solid #f1f5f9; padding-top: 12px;'>
                    <p style='margin: 4px 0;'><strong>Sender:</strong> {$this->senderName} (&lt;{$this->senderEmail}&gt;)</p>
                    <p style='margin: 4px 0;'><strong>Timestamp:</strong> " . now()->format('d M Y, h:i:s A T') . "</p>
                </div>
            </div>
        ";

        return $this->sendEmail($recipientEmail, $subject, $html);
    }

    /**
     * Send an instant security alert email when sensitive credentials or settings are modified.
     *
     * @param string $actionTitle e.g. "Razorpay API Credentials Updated"
     * @param array $metadata e.g. ['admin_name' => ..., 'admin_email' => ..., 'ip_address' => ..., 'changes' => ...]
     * @return array
     */
    public function sendSecurityAlert(string $actionTitle, array $metadata): array
    {
        $storeName = \App\Models\Setting::get('store_name', config('app.name', 'ShopCalm'));
        $recipient = \App\Models\Setting::get('contact_email') ?: config('mail.from.address');
        if (empty($recipient)) {
            return ['success' => false, 'message' => 'No alert recipient email configured'];
        }

        $subject = "🚨 [Security Alert] {$actionTitle} — {$storeName}";
        
        $adminName = htmlspecialchars($metadata['admin_name'] ?? 'Super Admin', ENT_QUOTES, 'UTF-8');
        $adminEmail = htmlspecialchars($metadata['admin_email'] ?? 'Unknown', ENT_QUOTES, 'UTF-8');
        $ip = htmlspecialchars($metadata['ip_address'] ?? 'Unknown IP', ENT_QUOTES, 'UTF-8');
        $userAgent = htmlspecialchars($metadata['user_agent'] ?? 'Unknown Agent', ENT_QUOTES, 'UTF-8');
        $timestamp = htmlspecialchars($metadata['timestamp'] ?? now()->toDayDateTimeString(), ENT_QUOTES, 'UTF-8');
        
        $changesList = '';
        foreach (($metadata['changes'] ?? []) as $field) {
            $changesList .= '<li style="margin-bottom: 5px;"><strong>' . htmlspecialchars($field, ENT_QUOTES, 'UTF-8') . '</strong></li>';
        }

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; margin: 0; padding: 24px;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <div style="background: #dc2626; padding: 20px 24px; color: #ffffff;">
            <h2 style="margin: 0; font-size: 20px; font-weight: 700;">🛡️ Security Alert Notification</h2>
            <p style="margin: 4px 0 0; font-size: 13px; opacity: 0.9;">{$storeName} Store Administration Hub</p>
        </div>
        <div style="padding: 24px; color: #1e293b; font-size: 14px; line-height: 1.6;">
            <p style="margin-top: 0; font-size: 15px; font-weight: 600; color: #0f172a;">
                Sensitive configuration credentials were just updated in your Store Administration Portal.
            </p>
            <div style="background: #f1f5f9; border-radius: 8px; padding: 16px; margin: 16px 0; border-left: 4px solid #dc2626;">
                <p style="margin: 0 0 8px;"><strong>Action:</strong> {$actionTitle}</p>
                <p style="margin: 0 0 8px;"><strong>Modified By:</strong> {$adminName} ({$adminEmail})</p>
                <p style="margin: 0 0 8px;"><strong>IP Address:</strong> {$ip}</p>
                <p style="margin: 0 0 8px;"><strong>Time:</strong> {$timestamp}</p>
                <p style="margin: 0 0 8px; word-break: break-all;"><strong>User Agent:</strong> {$userAgent}</p>
                <p style="margin: 10px 0 4px;"><strong>Fields Modified:</strong></p>
                <ul style="margin: 0; padding-left: 20px;">{$changesList}</ul>
            </div>
            <p style="font-size: 12px; color: #64748b; margin-bottom: 0;">
                If you or an authorized team member performed this action, no further steps are required.<br>
                If you did not authorize this change, please log in immediately to your <a href="https://hub.shopcalm.in/admin" style="color: #4f46e5; text-decoration: underline;">Staff Hub</a>, rotate your API keys, and update your administrator credentials.
            </p>
        </div>
        <div style="background: #f8fafc; padding: 14px 24px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center;">
            This is an automated security audit email from {$storeName}.
        </div>
    </div>
</body>
</html>
HTML;

        return $this->sendEmail($recipient, $subject, $html);
    }
}
