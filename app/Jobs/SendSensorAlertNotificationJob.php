<?php

namespace App\Jobs;

use App\Notifications\SensorAlertNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Delivers the operational alert produced by the AI analysis.
 *
 * Email is sent through the configured mailer (the demo uses the "log" mailer,
 * which writes the full message to storage/logs). Slack is simulated: when a
 * webhook URL is configured a realistic payload is POSTed, otherwise it is
 * simply logged so the flow can be demonstrated without external services.
 */
class SendSensorAlertNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [15, 45];

    /**
     * @param  array<string, mixed>  $verdict
     */
    public function __construct(
        public array $verdict,
        public ?string $esp32Id = null,
        public ?string $analysisId = null,
    ) {}

    public function handle(): void
    {
        $recipient = config('services.sensor_alerts.mail_to');

        if (! blank($recipient)) {
            Notification::route('mail', $recipient)
                ->notify(new SensorAlertNotification($this->verdict, $this->esp32Id, $this->analysisId));
        }

        $this->simulateSlackAlert();
    }

    /**
     * Post (or log) a Slack-style alert payload.
     */
    private function simulateSlackAlert(): void
    {
        $status = $this->verdict['status'] ?? 'WARNING';

        $payload = [
            'text' => sprintf('[SADIOT] %s alert on %s', $status, $this->esp32Id ?? 'unknown device'),
            'attachments' => [[
                'color' => $this->colorFor($status),
                'fields' => [
                    ['title' => 'Summary', 'value' => $this->verdict['summary'] ?? '', 'short' => false],
                    ['title' => 'Recommended action', 'value' => $this->verdict['recommended_action'] ?? '', 'short' => false],
                ],
            ]],
        ];

        $webhook = config('services.sensor_alerts.slack_webhook') ?: config('services.slack.webhook_url');

        if (blank($webhook)) {
            Log::info('[Slack simulation] '.json_encode($payload));

            return;
        }

        Http::timeout(10)->post($webhook, $payload);
    }

    private function colorFor(string $status): string
    {
        return match ($status) {
            'CRITICAL' => 'danger',
            'WARNING' => 'warning',
            default => 'good',
        };
    }
}
