<?php

namespace App\Exports;

use App\Models\SensorData;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class DataCutExport implements FromCollection, WithMapping, WithHeadings, ShouldQueue
{
    protected $deviceId;
    protected $date;

    public function __construct(string $deviceId, string $date)
    {
        $this->deviceId = $deviceId;
        $this->date     = $date;
    }

    public function collection()
    {
        return SensorData::with('sensor')
            ->where('device_id', $this->deviceId)
            ->where('dia', $this->date)
            ->orderBy('sensor_id')
            ->orderBy('timestamp')
            ->get();
    }

    public function map($row): array
    {
        return [
            optional(\Carbon\Carbon::parse($row->timestamp)->timezone('America/Cancun'))->format('H:i:s'),
            optional($row->sensor)->sensor_uid,
            ucfirst(optional($row->sensor)->tipo),
            $row->valor,
        ];
    }

    public function headings(): array
    {
        return ['Hora', 'Sensor UID', 'Tipo', 'Valor'];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $data  = $this->collection();
                $currentRow = 2; // empieza después del encabezado
                $lastSensor = null;

                foreach ($data as $item) {
                    if ($lastSensor && $lastSensor !== $item->sensor_id) {
                        // Inserta un salto de página antes de cambiar de sensor
                        $sheet->setBreak("A{$currentRow}");
                    }
                    $lastSensor = $item->sensor_id;
                    $currentRow++;
                }
            },
        ];
    }
}