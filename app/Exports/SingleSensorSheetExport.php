<?php
namespace App\Exports;

use App\Models\SensorData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SingleSensorSheetExport implements FromCollection, WithMapping, WithHeadings
{
    protected $deviceId;
    protected $sensorId;
    protected $date;

    public function __construct($deviceId, $sensorId, $date)
    {
        $this->deviceId = $deviceId;
        $this->sensorId = $sensorId;
        $this->date     = $date;
    }

    public function collection()
    {
        return SensorData::with('sensor')
            ->where('device_id', $this->deviceId)
            ->where('sensor_id', $this->sensorId)
            ->where('dia', $this->date)
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
}

