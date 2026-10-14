<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TelemetryAnalyzeRequest;
use App\Jobs\ProcessTelemetryJob;
use App\Models\AiAnalysis;
use App\Services\AiAnalysisService;
use Illuminate\Http\JsonResponse;

/**
 * AI-powered telemetry analysis endpoint.
 *
 * The response is produced synchronously for immediate feedback, while the
 * durable ingestion and operator notification are delegated to the queue.
 */
class TelemetryAiController extends Controller
{
    public function __construct(
        private readonly AiAnalysisService $ai,
    ) {}

    /**
     * POST /api/v1/telemetry/analyze
     */
    public function analyze(TelemetryAnalyzeRequest $request): JsonResponse
    {
        $readings = $request->input('readings');
        $esp32Id = (string) $request->input('esp32_id');
        $source = (string) $request->input('source', 'api');

        // Synchronous verdict for an immediate response.
        $verdict = $this->ai->analyzeBatch($readings, [
            'esp32_id' => $esp32Id,
            'source' => $source,
        ]);

        // Persist the verdict for auditing/history.
        $analysis = AiAnalysis::create([
            'device_id' => null,
            'esp32_id' => $esp32Id,
            'status' => $verdict['status'],
            'summary' => $verdict['summary'],
            'recommended_action' => $verdict['recommended_action'],
            'metrics' => $verdict['metrics'],
            'source' => $verdict['source'],
            'model' => $verdict['model'],
            'readings_count' => count($readings),
            'generated_at' => $verdict['generated_at'],
        ]);

        // Delegate ingestion + notification to the queue (reusing the verdict,
        // so the AI provider is only called once).
        ProcessTelemetryJob::dispatch(
            $readings,
            $esp32Id,
            $source,
            $verdict,
            (string) $analysis->_id,
        )->onQueue('telemetry');

        return response()->json([
            'status' => $verdict['status'],
            'summary' => $verdict['summary'],
            'recommended_action' => $verdict['recommended_action'],
            'meta' => [
                'analysis_id' => (string) $analysis->_id,
                'device' => $esp32Id,
                'source' => $verdict['source'],
                'model' => $verdict['model'],
                'metrics' => $verdict['metrics'],
                'queued' => true,
                'generated_at' => $verdict['generated_at'],
            ],
        ]);
    }

    /**
     * GET /api/v1/telemetry/analysis/{id}
     */
    public function show(string $id): JsonResponse
    {
        $analysis = AiAnalysis::find($id);

        if (! $analysis) {
            return response()->json(['message' => 'Analysis not found.'], 404);
        }

        return response()->json([
            'status' => $analysis->status,
            'summary' => $analysis->summary,
            'recommended_action' => $analysis->recommended_action,
            'meta' => [
                'analysis_id' => (string) $analysis->_id,
                'device' => $analysis->esp32_id,
                'source' => $analysis->source,
                'model' => $analysis->model,
                'metrics' => $analysis->metrics,
                'generated_at' => optional($analysis->generated_at)->toIso8601String(),
            ],
        ]);
    }
}
