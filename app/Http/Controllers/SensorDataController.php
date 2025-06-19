<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Sensor;
use App\Models\SensorData;

class SensorDataController extends Controller
{
    public function store(Request $req)
    {
        $data = $req->validate([
            'esp32_id'   => 'required|string|exists:devices,esp32_id',
            'sensor_uid' => 'required|string',
            'tipo'       => 'required|string',
            'valor'      => 'required|numeric',
            'timestamp'  => 'required|date',
        ]);

        // Relaciona al dispositivo
        $device = Device::where('esp32_id', $data['esp32_id'])->first();
        
        // Sensor: crea o actualiza
        $sensor = Sensor::updateOrCreate(
            ['sensor_uid' => $data['sensor_uid']],
            ['tipo' => $data['tipo'], 'device_id' => $device->id]
        );

        // Guarda lectura
        SensorData::create([
            'sensor_uid' => $sensor->sensor_uid,
            'valor'      => $data['valor'],
            'timestamp'  => $data['timestamp'],
        ]);

        return response()->json(['success' => true]);
    }
}
