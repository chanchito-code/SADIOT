<?php

namespace App\Exports;

use App\Models\Sensor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Carbon\Carbon;        // IMPORTANTE

class SensorDataExport implements FromCollection, WithHeadings
{
    protected string $sensorUid;

    public function __construct(string $sensorUid)
    {
        $this->sensorUid = $sensorUid;
    }

    public function collection(): Collection
    {
        $sensor = Sensor::where('sensor_uid', $this->sensorUid)->firstOrFail();

        return $sensor->data()
                     ->orderBy('timestamp', 'asc')
                     ->get(['timestamp', 'valor'])
                     ->map(function ($row) {
                         return [
                             'Fecha y hora' => Carbon::parse($row->timestamp)
                                                      ->format('Y-m-d H:i:s'),
                             'Valor'        => $row->valor,
                         ];
                     });
    }

    public function headings(): array
    {
        return [
            'Fecha y hora',
            'Valor',
        ];
    }
}
