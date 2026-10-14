<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an inbound telemetry batch for the AI analysis endpoint.
 *
 * Note: the device identifier is validated as a plain string rather than with
 * an `exists` rule. In IoT environments telemetry can arrive before a device
 * has been provisioned, so ingestion stays resilient; unknown devices are
 * reported by the ingest service instead of failing the whole request.
 */
class TelemetryAnalyzeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'esp32_id' => ['required', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:50'],
            'readings' => ['required', 'array', 'min:1', 'max:500'],
            'readings.*.sensor_uid' => ['required', 'string', 'max:100'],
            'readings.*.tipo' => ['required', 'string', 'max:50'],
            'readings.*.valor' => ['nullable', 'numeric'],
            'readings.*.timestamp' => ['nullable', 'date'],
            'readings.*.error' => ['nullable', 'boolean'],
        ];
    }
}
