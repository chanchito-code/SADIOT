<?php

namespace App\Exports;

use App\Models\SensorData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DataCutExport implements FromCollection, WithMapping, WithHeadings, ShouldQueue
{
    protected $deviceId;
    protected $date;

    public function __construct(string $deviceId, string $date)
    {
        $this->deviceId = $deviceId;
        $this->date     = $date; // formato YYYY-MM-DD
    }

    /**
     * Recoge la colección de lecturas para Excel.
     */
    public function collection()
    {
        return SensorData::with('sensor')
            ->where('device_id', $this->deviceId)
            ->where('dia', $this->date)
            ->orderBy('timestamp', 'asc')
            ->get();
    }

    /**
     * Mapea cada fila al formato deseado.
     */
    public function map($row): array
    {
        return [
            // Hora en local Cancún
            optional(\Carbon\Carbon::parse($row->timestamp)
                  ->timezone('America/Cancun'))
                ->format('H:i:s'),
            // Sensor UID
            optional($row->sensor)->sensor_uid,
            // Tipo
            ucfirst(optional($row->sensor)->tipo),
            // Valor
            $row->valor,
        ];
    }

    /**
     * Encabezados de las columnas.
     */
    public function headings(): array
    {
        return [
            'Hora',
            'Sensor UID',
            'Tipo',
            'Valor',
        ];
    }
}
