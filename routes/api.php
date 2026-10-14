<?php

use App\Http\Controllers\Api\SensorDataController;
use App\Http\Controllers\Api\TelemetryAiController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| SADIOT exposes a versioned REST API for IoT telemetry ingestion and
| AI-powered analysis. All v1 endpoints are rate limited to protect the
| datastore from abusive or misconfigured devices.
|
*/

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    // AI analysis of a telemetry batch (returns a structured verdict).
    Route::post('telemetry/analyze', [TelemetryAiController::class, 'analyze']);

    // Retrieve a previously stored analysis verdict.
    Route::get('telemetry/analysis/{id}', [TelemetryAiController::class, 'show']);

    // Inbound alert webhook (asynchronous ingestion + AI + notifications).
    Route::post('webhooks/sensor-alert', [WebhookController::class, 'sensorAlert']);
});

// Legacy ESP32 ingestion endpoint (preserved for backwards compatibility).
Route::post('sensors/data', [SensorDataController::class, 'store']);
