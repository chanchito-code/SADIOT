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

        // Buscamos el device por esp32_id
        $device = Device::where('esp32_id', $data['esp32_id'])->firstOrFail();

        // Creamos o actualizamos sensor, asegurando siempre el device_id
        $sensor = Sensor::updateOrCreate(
            ['sensor_uid' => $data['sensor_uid'], 'device_id' => (string) $device->_id],
            ['tipo'       => $data['tipo']]
        );

        // Grabamos la lectura, ahora con sensor_id y device_id
        SensorData::create([
            'sensor_id' => (string) $sensor->_id,
            'device_id' => (string) $device->_id,
            'valor'     => $data['valor'],
            'timestamp' => $data['timestamp'],
            // 'dia' se rellenará automáticamente en el hook booted()
        ]);

        return response()->json(['success' => true], 201);
    }
}
