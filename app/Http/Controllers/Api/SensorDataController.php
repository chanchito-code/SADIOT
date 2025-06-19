<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Sensor;
use App\Models\SensorData;

class SensorDataController extends Controller
{
    public function store(Request $request)
    {
        // Validamos JSON puro; no usamos CSRF ni sesiones
        $data = $request->validate([
            'esp32_id'   => 'required|string|exists:devices,esp32_id',
            'sensor_uid' => 'required|string',
            'tipo'       => 'required|string',
            'valor'      => 'required|numeric',
            'timestamp'  => 'required|date',
        ]);

        // Buscamos el device
        $device = Device::where('esp32_id', $data['esp32_id'])->first();

        // Creamos o actualizamos sensor
        $sensor = Sensor::updateOrCreate(
            ['sensor_uid' => $data['sensor_uid']],
            ['tipo' => $data['tipo'], 'device_id' => $device->id]
        );

        // Grabamos la lectura
        SensorData::create([
            'sensor_uid' => $sensor->sensor_uid,
            'valor'      => $data['valor'],
            'timestamp'  => $data['timestamp'],
        ]);

        return response()->json(['success' => true], 201);
    }
}
