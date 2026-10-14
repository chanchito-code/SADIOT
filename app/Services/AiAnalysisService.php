<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/**
 * Turns a batch of IoT telemetry into an executive, operator-ready verdict.
 *
 * The service calls the OpenAI Chat Completions API in JSON mode and always
 * returns the same structured contract:
 *
 *   ['status' => NORMAL|WARNING|CRITICAL, 'summary' => string, 'recommended_action' => string]
 *
 * When the AI is disabled, unconfigured or failing, a deterministic heuristic
 * verdict is returned instead so callers can rely on a stable response shape.
 */
class AiAnalysisService
{
    public function __construct(
        private readonly TelemetryAnomalyDetector $detector,
    ) {}

    /**
     * Analyse a telemetry batch and return a structured verdict.
     *
     * @param  array<int, array<string, mixed>>  $readings
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function analyzeBatch(array $readings, array $context = []): array
    {
        $metrics = $this->detector->inspect($readings);

        if (! $this->isEnabled() || blank(config('services.openai.api_key'))) {
            return $this->heuristicVerdict($metrics, $context, 'AI disabled or API key missing.');
        }

        try {
            return $this->requestOpenAi($readings, $metrics, $context);
        } catch (Throwable $exception) {
            Log::warning('OpenAI telemetry analysis failed; using heuristic fallback.', [
                'error' => $exception->getMessage(),
            ]);

            return $this->heuristicVerdict($metrics, $context, $exception->getMessage());
        }
    }

    /**
     * Whether the AI layer is globally enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) config('services.openai.enabled', true);
    }

    /**
     * Perform the OpenAI request using the raw HTTP client (no SDK required).
     *
     * @param  array<int, array<string, mixed>>  $readings
     * @param  array<string, mixed>  $metrics
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function requestOpenAi(array $readings, array $metrics, array $context): array
    {
        $response = Http::withToken((string) config('services.openai.api_key'))
            ->timeout((int) config('services.openai.timeout', 30))
            ->acceptJson()
            ->post(rtrim((string) config('services.openai.base_url', 'https://api.openai.com/v1'), '/').'/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $this->userPrompt($readings, $metrics, $context)],
                ],
            ])
            ->throw();

        $content = $response->json('choices.0.message.content');
        $decoded = is_string($content) ? json_decode($content, true) : null;

        if (! is_array($decoded)
            || ! isset($decoded['status'], $decoded['summary'], $decoded['recommended_action'])) {
            throw new RuntimeException('Malformed AI response payload.');
        }

        return $this->formatResult(
            status: $this->normaliseStatus((string) $decoded['status']),
            summary: trim((string) $decoded['summary']),
            action: trim((string) $decoded['recommended_action']),
            metrics: $metrics,
            source: 'openai',
            model: (string) config('services.openai.model', 'gpt-4o-mini'),
        );
    }

    /**
     * Build a deterministic verdict without calling the AI provider.
     *
     * @param  array<string, mixed>  $metrics
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    private function heuristicVerdict(array $metrics, array $context, string $reason): array
    {
        $status = $this->detector->severity($metrics);
        $device = $context['esp32_id'] ?? 'unknown device';

        $summaries = [
            TelemetryAnomalyDetector::STATUS_NORMAL => sprintf(
                'Batch from %s processed successfully: %d readings ingested with no anomalies detected.',
                $device,
                $metrics['total_readings']
            ),
            TelemetryAnomalyDetector::STATUS_WARNING => sprintf(
                'Batch from %s shows early signs of degradation: %d read error(s) and %d temperature spike(s).',
                $device,
                $metrics['read_errors'],
                $metrics['temperature_celsius']['spikes']
            ),
            TelemetryAnomalyDetector::STATUS_CRITICAL => sprintf(
                'Batch from %s requires immediate attention: temperature peaked at %s C with a read error ratio of %s.',
                $device,
                $metrics['temperature_celsius']['max'] ?? 'n/a',
                $metrics['read_error_ratio']
            ),
        ];

        $actions = [
            TelemetryAnomalyDetector::STATUS_NORMAL => 'No action required. Continue standard monitoring.',
            TelemetryAnomalyDetector::STATUS_WARNING => 'Inspect the sensor wiring and verify the calibration on the affected device.',
            TelemetryAnomalyDetector::STATUS_CRITICAL => 'Dispatch a technician, isolate the device and validate cooling/ventilation immediately.',
        ];

        return $this->formatResult(
            status: $status,
            summary: $summaries[$status],
            action: $actions[$status],
            metrics: $metrics,
            source: 'heuristic',
            model: null,
            note: $reason,
        );
    }

    /**
     * Normalise every verdict into the public response contract.
     *
     * @param  array<string, mixed>  $metrics
     * @return array<string, mixed>
     */
    private function formatResult(
        string $status,
        string $summary,
        string $action,
        array $metrics,
        string $source,
        ?string $model,
        ?string $note = null,
    ): array {
        $result = [
            'status' => $status,
            'summary' => $summary,
            'recommended_action' => $action,
            'metrics' => $metrics,
            'source' => $source,
            'model' => $model,
            'generated_at' => now()->toIso8601String(),
        ];

        if ($note !== null) {
            $result['note'] = $note;
        }

        return $result;
    }

    private function normaliseStatus(string $status): string
    {
        $status = strtoupper(trim($status));

        return in_array($status, [
            TelemetryAnomalyDetector::STATUS_NORMAL,
            TelemetryAnomalyDetector::STATUS_WARNING,
            TelemetryAnomalyDetector::STATUS_CRITICAL,
        ], true) ? $status : TelemetryAnomalyDetector::STATUS_WARNING;
    }

    private function systemPrompt(): string
    {
        return 'You are an expert IoT reliability engineer. '
            .'Analyse the telemetry metrics and respond ONLY with a JSON object '
            .'containing exactly these keys: '
            .'"status" (one of "NORMAL", "WARNING", "CRITICAL"), '
            .'"summary" (concise technical English, maximum 60 words), '
            .'"recommended_action" (an imperative instruction for the on-call operator, maximum 40 words).';
    }

    /**
     * @param  array<int, array<string, mixed>>  $readings
     * @param  array<string, mixed>  $metrics
     * @param  array<string, mixed>  $context
     */
    private function userPrompt(array $readings, array $metrics, array $context): string
    {
        $sample = array_slice($readings, 0, 20);

        return "Device: {$context['esp32_id']}\n"
            .'Source: '.($context['source'] ?? 'api')."\n\n"
            ."Computed metrics (JSON):\n".json_encode($metrics, JSON_PRETTY_PRINT)."\n\n"
            ."Sample readings (first 20):\n".json_encode($sample, JSON_PRETTY_PRINT);
    }
}
