<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Operator-facing alert generated from an AI telemetry verdict.
 */
class SensorAlertNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $verdict
     */
    public function __construct(
        public array $verdict,
        public ?string $esp32Id = null,
        public ?string $analysisId = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = $this->verdict['status'] ?? 'WARNING';

        return (new MailMessage)
            ->subject(sprintf('[SADIOT] %s alert on %s', $status, $this->esp32Id ?? 'unknown device'))
            ->greeting('SADIOT Telemetry Alert')
            ->line('Status: '.$status)
            ->line('Device: '.($this->esp32Id ?? 'unknown'))
            ->line('Summary: '.($this->verdict['summary'] ?? 'n/a'))
            ->line('Recommended action: '.($this->verdict['recommended_action'] ?? 'n/a'))
            ->line('Analysis reference: '.($this->analysisId ?? 'n/a'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'status' => $this->verdict['status'] ?? null,
            'esp32_id' => $this->esp32Id,
            'analysis_id' => $this->analysisId,
            'summary' => $this->verdict['summary'] ?? null,
            'recommended_action' => $this->verdict['recommended_action'] ?? null,
        ];
    }
}
