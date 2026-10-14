<?php

namespace App\Services;

use App\Models\Device;
use App\Models\Sensor;
use App\Models\SensorData;
use InvalidArgumentException;
use Throwable;

/**
 * Encapsulates telemetry ingestion so both the synchronous ESP32 endpoint and
 * the asynchronous queue worker share exactly the same persistence logic.
 */
class TelemetryIngestService
{
    /**
     * Persist every readable item of a batch.
     *
     * Invalid or orphaned readings are skipped (never aborting the batch) and
     * reported back to the caller for observability.
     *
     * @param  array<int, array<string, mixed>>  $readings
     * @return array{stored:int, skipped:int, device_id:?string, errors:array<int, array<string, mixed>>}
     */
    public function ingestBatch(array $readings, ?string $esp32Id = null): array
    {
        $stored = 0;
        $errors = [];
        $deviceId = null;

        foreach ($readings as $index => $reading) {
            if ($this->hasReadError($reading)) {
                $errors[] = ['index' => $index, 'reason' => 'Unreadable telemetry value.'];

                continue;
            }

            try {
                $data = $this->storeReading($reading, $esp32Id);
                $stored++;
                $deviceId ??= (string) $data->device_id;
            } catch (Throwable $exception) {
                $errors[] = ['index' => $index, 'reason' => $exception->getMessage()];
            }
        }

        return [
            'stored' => $stored,
            'skipped' => count($errors),
            'device_id' => $deviceId,
            'errors' => $errors,
        ];
    }

    /**
     * Resolve the sensor (upsert) and persist a single reading in MongoDB.
     *
     * @param  array<string, mixed>  $reading
     */
    public function storeReading(array $reading, ?string $esp32Id = null): SensorData
    {
        $esp32Id = $reading['esp32_id'] ?? $esp32Id;

        if (blank($esp32Id)) {
            throw new InvalidArgumentException('Missing esp32_id for telemetry reading.');
        }

        $device = Device::where('esp32_id', $esp32Id)->first();

        if (! $device) {
            throw new InvalidArgumentException("Unknown device: {$esp32Id}.");
        }

        $sensor = Sensor::updateOrCreate(
            [
                'sensor_uid' => $reading['sensor_uid'],
                'device_id' => (string) $device->_id,
            ],
            [
                'tipo' => $reading['tipo'] ?? 'unknown',
            ]
        );

        return SensorData::create([
            'sensor_id' => (string) $sensor->_id,
            'device_id' => (string) $device->_id,
            'valor' => $reading['valor'] ?? null,
            'timestamp' => $reading['timestamp'] ?? now(),
        ]);
    }

    /**
     * Mirror of the detector's rule: a reading is invalid when flagged as an
     * error or when the numeric value is missing.
     *
     * @param  array<string, mixed>  $reading
     */
    private function hasReadError(array $reading): bool
    {
        if (($reading['error'] ?? false) === true) {
            return true;
        }

        return ! array_key_exists('valor', $reading) || ! is_numeric($reading['valor']);
    }
}
