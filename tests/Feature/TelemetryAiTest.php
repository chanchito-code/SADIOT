<?php

namespace Tests\Feature;

use App\Jobs\ProcessTelemetryJob;
use App\Models\Device;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Feature tests for POST /api/v1/telemetry/analyze.
 *
 * Requires a reachable MongoDB instance; the suite is skipped otherwise so the
 * rest of the test suite can still run offline.
 */
class TelemetryAiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        try {
            Device::query()->limit(1)->get();
        } catch (\Throwable $exception) {
            $this->markTestSkipped('MongoDB is not available: '.$exception->getMessage());
        }
    }

    public function test_it_returns_a_structured_verdict_and_queues_processing(): void
    {
        config([
            'services.openai.enabled' => true,
            'services.openai.api_key' => 'test-key',
        ]);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'status' => 'CRITICAL',
                        'summary' => 'Temperature spiked above the safe threshold.',
                        'recommended_action' => 'Shut down and inspect the device.',
                    ])],
                ]],
            ], 200),
        ]);

        Queue::fake();

        $response = $this->postJson('/api/v1/telemetry/analyze', [
            'esp32_id' => 'ESP32-TEST',
            'source' => 'phpunit',
            'readings' => [
                ['sensor_uid' => 'temp-1', 'tipo' => 'temperatura', 'valor' => 95, 'timestamp' => now()->toIso8601String()],
                ['sensor_uid' => 'temp-2', 'tipo' => 'temperatura', 'valor' => 97, 'timestamp' => now()->toIso8601String()],
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'summary',
                'recommended_action',
                'meta' => ['analysis_id', 'device', 'source', 'model', 'metrics'],
            ])
            ->assertJsonPath('status', 'CRITICAL');

        Queue::assertPushed(ProcessTelemetryJob::class);
    }

    public function test_it_validates_the_payload(): void
    {
        $this->postJson('/api/v1/telemetry/analyze', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['esp32_id', 'readings']);
    }
}
