<?php

namespace App\Services;

/**
 * Computes deterministic health metrics from a batch of IoT readings.
 *
 * The detector is intentionally framework-agnostic. It is reused to both
 * enrich the LLM prompt and to produce a safe fallback verdict whenever the
 * AI provider is unavailable, so the platform never stops responding.
 */
class TelemetryAnomalyDetector
{
    /** Temperature (Celsius) at which a reading is considered a spike. */
    public const TEMP_WARNING = 60.0;

    /** Temperature (Celsius) at which the situation becomes critical. */
    public const TEMP_CRITICAL = 80.0;

    /** Ratio of unreadable samples that triggers a warning. */
    public const READ_ERROR_RATIO_WARNING = 0.10;

    /** Ratio of unreadable samples that triggers a critical state. */
    public const READ_ERROR_RATIO_CRITICAL = 0.30;

    public const STATUS_NORMAL = 'NORMAL';

    public const STATUS_WARNING = 'WARNING';

    public const STATUS_CRITICAL = 'CRITICAL';

    /**
     * Inspect a batch of readings and return aggregated metrics.
     *
     * @param  array<int, array<string, mixed>>  $readings
     * @return array<string, mixed>
     */
    public function inspect(array $readings): array
    {
        $total = count($readings);
        $readErrors = 0;
        $validValues = [];
        $temperatures = [];
        $byType = [];

        foreach ($readings as $reading) {
            $type = $this->normaliseType((string) ($reading['tipo'] ?? $reading['type'] ?? 'unknown'));
            $byType[$type] = ($byType[$type] ?? 0) + 1;

            if ($this->hasReadError($reading)) {
                $readErrors++;

                continue;
            }

            $value = (float) $reading['valor'];
            $validValues[] = $value;

            if ($this->isTemperature($type)) {
                $temperatures[] = $value;
            }
        }

        return [
            'total_readings' => $total,
            'valid_readings' => count($validValues),
            'read_errors' => $readErrors,
            'read_error_ratio' => $total > 0 ? round($readErrors / $total, 2) : 0.0,
            'readings_by_type' => $byType,
            'temperature_celsius' => [
                'samples' => count($temperatures),
                'min' => $temperatures === [] ? null : min($temperatures),
                'max' => $temperatures === [] ? null : max($temperatures),
                'avg' => $temperatures === [] ? null : round(array_sum($temperatures) / count($temperatures), 2),
                'spikes' => count(array_filter(
                    $temperatures,
                    fn (float $temp): bool => $temp >= self::TEMP_WARNING
                )),
            ],
            'value' => [
                'min' => $validValues === [] ? null : min($validValues),
                'max' => $validValues === [] ? null : max($validValues),
                'avg' => $validValues === [] ? null : round(array_sum($validValues) / count($validValues), 2),
            ],
        ];
    }

    /**
     * Derive a severity level from previously computed metrics.
     *
     * @param  array<string, mixed>  $metrics
     */
    public function severity(array $metrics): string
    {
        $maxTemp = $metrics['temperature_celsius']['max'] ?? null;
        $errorRatio = (float) ($metrics['read_error_ratio'] ?? 0.0);
        $readErrors = (int) ($metrics['read_errors'] ?? 0);

        if (($maxTemp !== null && $maxTemp >= self::TEMP_CRITICAL)
            || $errorRatio >= self::READ_ERROR_RATIO_CRITICAL) {
            return self::STATUS_CRITICAL;
        }

        if (($maxTemp !== null && $maxTemp >= self::TEMP_WARNING)
            || $errorRatio >= self::READ_ERROR_RATIO_WARNING
            || $readErrors > 0) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_NORMAL;
    }

    /**
     * Determine whether a single reading is unreadable/corrupted.
     *
     * @param  array<string, mixed>  $reading
     */
    public function hasReadError(array $reading): bool
    {
        if (($reading['error'] ?? false) === true) {
            return true;
        }

        return ! array_key_exists('valor', $reading) || ! is_numeric($reading['valor']);
    }

    private function normaliseType(string $type): string
    {
        return strtolower(trim($type));
    }

    /**
     * Heuristic detection of temperature-like sensor types.
     */
    private function isTemperature(string $type): bool
    {
        foreach (['temp', 'temperatura', 'temperature'] as $needle) {
            if (str_contains($type, $needle)) {
                return true;
            }
        }

        return false;
    }
}
