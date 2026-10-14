<?php

namespace Tests\Feature;

use App\Jobs\ProcessTelemetryJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Feature tests for the inbound sensor-alert webhook.
 */
class SensorAlertWebhookTest extends TestCase
{
    public function test_it_accepts_an_alert_and_queues_the_ai_pipeline(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/webhooks/sensor-alert', [
            'esp32_id' => 'ESP32-TEST',
            'severity' => 'high',
            'message' => 'External threshold exceeded',
            'readings' => [
                ['sensor_uid' => 'temp-1', 'tipo' => 'temperatura', 'valor' => 88, 'timestamp' => now()->toIso8601String()],
            ],
        ]);

        $response
            ->assertStatus(202)
            ->assertJsonPath('status', 'QUEUED')
            ->assertJsonPath('device', 'ESP32-TEST');

        Queue::assertPushed(ProcessTelemetryJob::class, function (ProcessTelemetryJob $job): bool {
            return $job->esp32Id === 'ESP32-TEST' && $job->source === 'webhook';
        });
    }

    public function test_it_requires_a_device_and_readings(): void
    {
        $this->postJson('/api/v1/webhooks/sensor-alert', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['esp32_id', 'readings']);
    }
}
