<?php

namespace App\Exports;

use App\Models\Sensor;
use App\Models\SensorData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use MongoDB\BSON\UTCDateTime as MongoUTC;

class SensorDataExport implements FromCollection, WithHeadings
{
    protected string $identifier; // puede ser sensor_uid o _id (string)

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function collection(): Collection
    {
        // Intentar buscar sensor por sensor_uid o por _id (string)
        $sensor = Sensor::where('sensor_uid', $this->identifier)
                        ->orWhere('_id', $this->identifier)
                        ->first();

        // Si encontramos sensor, intentar buscar sensor_data que referencien su _id o sensor_uid
        if ($sensor) {
            $sensorKey = $sensor->_id ?? $sensor->getKey();
            $sensorKeyString = (string) $sensorKey;
            $sensorUid = $sensor->sensor_uid ?? null;

            $rows = SensorData::where(function($q) use ($sensorKey, $sensorKeyString, $sensorUid) {
                $q->where('sensor_id', $sensorKey)
                  ->orWhere('sensor_id', $sensorKeyString);

                if ($sensorUid) {
                    $q->orWhere('sensor_id', $sensorUid)
                      ->orWhere('sensor_uid', $sensorUid);
                }
            })->orderBy('timestamp', 'asc')->get();
        } else {
            // Si no hay sensor, asumimos que el identificador podría ser el sensor_id guardado en los documentos
            $id = $this->identifier;
            $rows = SensorData::where('sensor_id', $id)
                              ->orWhere('sensor_uid', $id)
                              ->orderBy('timestamp', 'asc')
                              ->get();
        }

        return $rows->map(function ($row) {
            // Normalizar timestamp
            $ts = $row->timestamp ?? null;
            if ($ts instanceof MongoUTC) {
                $dt = $ts->toDateTime();
                $carbon = Carbon::instance($dt);
            } elseif ($ts instanceof \DateTimeInterface) {
                $carbon = Carbon::instance($ts);
            } else {
                try {
                    $carbon = Carbon::parse($ts);
                } catch (\Throwable $e) {
                    $carbon = Carbon::now();
                }
            }

            // Normalizar valor
            $valor = $row->valor ?? null;
            if (is_array($valor)) {
                $valor = $valor['$numberDecimal'] ?? $valor['$numberLong'] ?? ($valor['value'] ?? json_encode($valor));
            } elseif (is_object($valor)) {
                $valor = method_exists($valor, '__toString') ? (string) $valor : json_encode($valor);
            }

            return [
                'Fecha y hora' => $carbon->format('Y-m-d H:i:s'),
                'Valor'        => $valor,
            ];
        });
    }

    public function headings(): array
    {
        return ['Fecha y hora','Valor'];
    }
}
