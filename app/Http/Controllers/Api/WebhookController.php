<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SensorAlertWebhookRequest;
use App\Jobs\ProcessTelemetryJob;
use Illuminate\Http\JsonResponse;

/**
 * Receives external sensor-alert events and triggers the asynchronous
 * AI analysis + notification pipeline.
 */
class WebhookController extends Controller
{
    /**
     * POST /api/v1/webhooks/sensor-alert
     */
    public function sensorAlert(SensorAlertWebhookRequest $request): JsonResponse
    {
        $readings = $request->input('readings');
        $esp32Id = (string) $request->input('esp32_id');

        // Fire-and-forget: ingestion, AI analysis and notifications happen on
        // the queue so the webhook returns instantly.
        ProcessTelemetryJob::dispatch($readings, $esp32Id, 'webhook')->onQueue('telemetry');

        return response()->json([
            'status' => 'QUEUED',
            'message' => 'Sensor alert received. AI analysis queued for asynchronous processing.',
            'device' => $esp32Id,
            'readings_received' => count($readings),
        ], JsonResponse::HTTP_ACCEPTED);
    }
}
