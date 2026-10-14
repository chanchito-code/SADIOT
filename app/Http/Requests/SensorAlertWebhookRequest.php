<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an external sensor-alert webhook payload.
 */
class SensorAlertWebhookRequest extends FormRequest
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
            'severity' => ['nullable', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:500'],
            'readings' => ['required', 'array', 'min:1', 'max:500'],
            'readings.*.sensor_uid' => ['required', 'string', 'max:100'],
            'readings.*.tipo' => ['required', 'string', 'max:50'],
            'readings.*.valor' => ['nullable', 'numeric'],
            'readings.*.timestamp' => ['nullable', 'date'],
            'readings.*.error' => ['nullable', 'boolean'],
        ];
    }
}
