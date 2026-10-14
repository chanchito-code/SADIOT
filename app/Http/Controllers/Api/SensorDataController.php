<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelemetryIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Legacy ESP32 ingestion endpoint.
 *
 * Kept for backwards compatibility with already-deployed firmware. The
 * persistence logic now lives in TelemetryIngestService so it is shared with
 * the asynchronous queue pipeline.
 */
class SensorDataController extends Controller
{
    public function __construct(
        private readonly TelemetryIngestService $ingest,
    ) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'esp32_id' => ['required', 'string', 'exists:mongodb.devices,esp32_id'],
            'sensor_uid' => ['required', 'string'],
            'tipo' => ['required', 'string'],
            'valor' => ['required', 'numeric'],
            'timestamp' => ['required', 'date'],
        ]);

        $this->ingest->storeReading($data);

        return response()->json(['success' => true], 201);
    }
}
