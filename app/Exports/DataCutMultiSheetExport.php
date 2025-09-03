<?php
namespace App\Exports;

use App\Models\SensorData;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DataCutMultiSheetExport implements WithMultipleSheets
{
    protected $deviceId;
    protected $date;

    public function __construct(string $deviceId, string $date)
    {
        $this->deviceId = $deviceId;
        $this->date     = $date;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sensorIds = SensorData::where('device_id', $this->deviceId)
            ->where('dia', $this->date)
            ->pluck('sensor_id')
            ->unique();

        foreach ($sensorIds as $sensorId) {
            $sheets[] = new SingleSensorSheetExport($this->deviceId, $sensorId, $this->date);
        }

        return $sheets;
    }
}
