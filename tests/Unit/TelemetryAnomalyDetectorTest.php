<?php

namespace Tests\Unit;

use App\Services\TelemetryAnomalyDetector;
use PHPUnit\Framework\TestCase;

/**
 * Pure unit tests for the deterministic anomaly detector (no framework, no DB).
 */
class TelemetryAnomalyDetectorTest extends TestCase
{
    private TelemetryAnomalyDetector $detector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->detector = new TelemetryAnomalyDetector;
    }

    public function test_normal_batch_is_classified_as_normal(): void
    {
        $metrics = $this->detector->inspect($this->temperatureReadings(24.5));

        $this->assertSame(TelemetryAnomalyDetector::STATUS_NORMAL, $this->detector->severity($metrics));
        $this->assertSame(3, $metrics['total_readings']);
        $this->assertSame(0, $metrics['read_errors']);
    }

    public function test_high_temperature_is_classified_as_critical(): void
    {
        $metrics = $this->detector->inspect($this->temperatureReadings(92.0));

        $this->assertSame(TelemetryAnomalyDetector::STATUS_CRITICAL, $this->detector->severity($metrics));
        $this->assertSame(92.0, $metrics['temperature_celsius']['max']);
    }

    public function test_temperature_between_thresholds_is_warning(): void
    {
        $metrics = $this->detector->inspect($this->temperatureReadings(65.0));

        $this->assertSame(TelemetryAnomalyDetector::STATUS_WARNING, $this->detector->severity($metrics));
    }

    public function test_read_errors_are_detected_and_escalated(): void
    {
        $metrics = $this->detector->inspect([
            ['sensor_uid' => 't1', 'tipo' => 'temperatura', 'valor' => 25.0],
            ['sensor_uid' => 't1', 'tipo' => 'temperatura', 'error' => true],
            ['sensor_uid' => 't1', 'tipo' => 'temperatura', 'valor' => null],
        ]);

        $this->assertSame(2, $metrics['read_errors']);
        $this->assertGreaterThan(0, $metrics['read_errors']);
        $this->assertNotSame(TelemetryAnomalyDetector::STATUS_NORMAL, $this->detector->severity($metrics));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function temperatureReadings(float $value): array
    {
        return [
            ['sensor_uid' => 't1', 'tipo' => 'temperatura', 'valor' => $value],
            ['sensor_uid' => 't2', 'tipo' => 'temperature', 'valor' => $value - 1],
            ['sensor_uid' => 't3', 'tipo' => 'humedad', 'valor' => 40],
        ];
    }
}
