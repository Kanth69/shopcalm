<?php

namespace App\Jobs;

use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBrevoEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // retry after 60 seconds

    public function __construct(
        private readonly string $to,
        private readonly string $subject,
        private readonly string $html,
        private readonly ?string $text = null
    ) {}

    public function handle(EmailService $emailService): void
    {
        $result = $emailService->sendEmail(
            to:      $this->to,
            subject: $this->subject,
            html:    $this->html,
            text:    $this->text,
        );

        if (!$result['success']) {
            $status = $result['status'] ?? 500;
            
            // Do not retry permanent failures
            if (in_array($status, [400, 401, 403])) {
                $this->fail(new \Exception("Brevo API returned a permanent failure (HTTP $status)."));
                return;
            }

            // For temporary failures (5xx, timeouts), throw exception to trigger a retry
            throw new \Exception("Brevo API returned a temporary failure (HTTP $status). Retrying...");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('[SendBrevoEmailJob] Job permanently failed after all retries.', [
            'to'        => $this->to,
            'subject'   => $this->subject,
            'exception' => $exception->getMessage(),
        ]);
    }
}
