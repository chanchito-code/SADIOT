<?php

namespace Tests\Unit;

use App\Services\AiAnalysisService;
use App\Services\TelemetryAnomalyDetector;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Tests the AI analysis contract and the offline heuristic fallback.
 */
class AiAnalysisServiceTest extends TestCase
{
    private function service(): AiAnalysisService
    {
        return new AiAnalysisService(new TelemetryAnomalyDetector);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readings(float $value = 95.0): array
    {
        return [
            ['sensor_uid' => 't1', 'tipo' => 'temperatura', 'valor' => $value],
        ];
    }

    public function test_it_falls_back_to_heuristic_when_api_key_is_missing(): void
    {
        config([
            'services.openai.enabled' => true,
            'services.openai.api_key' => null,
        ]);

        $result = $this->service()->analyzeBatch($this->readings(), ['esp32_id' => 'ESP32-1']);

        $this->assertSame(TelemetryAnomalyDetector::STATUS_CRITICAL, $result['status']);
        $this->assertSame('heuristic', $result['source']);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('recommended_action', $result);
    }

    public function test_it_parses_a_valid_openai_response(): void
    {
        config([
            'services.openai.enabled' => true,
            'services.openai.api_key' => 'test-key',
            'services.openai.model' => 'gpt-4o-mini',
        ]);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode([
                        'status' => 'CRITICAL',
                        'summary' => 'Peak temperature detected on the main board.',
                        'recommended_action' => 'Dispatch a technician immediately.',
                    ])],
                ]],
            ], 200),
        ]);

        $result = $this->service()->analyzeBatch($this->readings(), ['esp32_id' => 'ESP32-1']);

        $this->assertSame('CRITICAL', $result['status']);
        $this->assertSame('openai', $result['source']);
        $this->assertSame('gpt-4o-mini', $result['model']);
    }

    public function test_it_falls_back_to_heuristic_when_the_api_fails(): void
    {
        config([
            'services.openai.enabled' => true,
            'services.openai.api_key' => 'test-key',
        ]);

        Http::fake([
            'api.openai.com/*' => Http::response('Service unavailable', 503),
        ]);

        $result = $this->service()->analyzeBatch($this->readings(), ['esp32_id' => 'ESP32-1']);

        $this->assertSame('heuristic', $result['source']);
        $this->assertContains($result['status'], ['NORMAL', 'WARNING', 'CRITICAL']);
    }
}
