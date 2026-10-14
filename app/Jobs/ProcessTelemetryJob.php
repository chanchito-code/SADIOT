<?php

namespace App\Jobs;

use App\Models\AiAnalysis;
use App\Services\AiAnalysisService;
use App\Services\TelemetryIngestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Processes an incoming telemetry batch fully asynchronously:
 *
 *   1. Persists the raw readings into MongoDB (ingestion).
 *   2. Runs the AI analysis (OpenAI or heuristic fallback).
 *   3. Stores the resulting verdict in the `ai_analyses` collection.
 *   4. Chains an alert notification whenever the status is not NORMAL.
 *
 * Running this on the queue keeps the HTTP request fast and prevents heavy
 * writes / external API calls from becoming database or latency bottlenecks.
 */
class ProcessTelemetryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Number of attempts before the job is marked as failed. */
    public int $tries = 3;

    /** Maximum execution time (seconds) per attempt. */
    public int $timeout = 90;

    /** Exponential backoff between attempts (seconds). */
    public array $backoff = [10, 30, 60];

    /**
     * @param  array<int, array<string, mixed>>  $readings
     * @param  array<string, mixed>|null  $verdict  Precomputed verdict (skips the AI call).
     */
    public function __construct(
        public array $readings,
        public ?string $esp32Id = null,
        public string $source = 'api',
        public ?array $verdict = null,
        public ?string $analysisId = null,
    ) {}

    public function handle(TelemetryIngestService $ingest, AiAnalysisService $ai): void
    {
        // 1. Ingest raw telemetry.
        $stats = $ingest->ingestBatch($this->readings, $this->esp32Id);

        // 2. Reuse a precomputed verdict when provided, otherwise analyse now.
        $analysisId = $this->analysisId;

        if ($this->verdict !== null) {
            $verdict = $this->verdict;
        } else {
            $verdict = $ai->analyzeBatch($this->readings, [
                'esp32_id' => $this->esp32Id,
                'source' => $this->source,
            ]);

            // 3. Persist the analysis for auditing and history.
            $analysis = AiAnalysis::create([
                'device_id' => $stats['device_id'],
                'esp32_id' => $this->esp32Id,
                'status' => $verdict['status'],
                'summary' => $verdict['summary'],
                'recommended_action' => $verdict['recommended_action'],
                'metrics' => $verdict['metrics'],
                'source' => $verdict['source'],
                'model' => $verdict['model'],
                'readings_count' => count($this->readings),
                'stored_count' => $stats['stored'],
                'generated_at' => $verdict['generated_at'],
            ]);

            $analysisId = (string) $analysis->_id;
        }

        // 4. Notify the operator only when something is off.
        if ($verdict['status'] !== AiAnalysis::STATUS_NORMAL) {
            SendSensorAlertNotificationJob::dispatch(
                $verdict,
                $this->esp32Id,
                $analysisId,
            )->onQueue('notifications');
        }

        Log::info('Telemetry batch processed.', [
            'esp32_id' => $this->esp32Id,
            'status' => $verdict['status'],
            'stored' => $stats['stored'],
            'skipped' => $stats['skipped'],
            'source' => $verdict['source'],
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('ProcessTelemetryJob failed.', [
            'esp32_id' => $this->esp32Id,
            'error' => $exception->getMessage(),
        ]);
    }
}
