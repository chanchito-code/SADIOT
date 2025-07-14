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

    // Buscar el dispositivo por esp32_id
    $device = Device::where('esp32_id', $data['esp32_id'])->first();

    // Crear o actualizar sensor con device_id y sensor_uid como clave compuesta
    $sensor = Sensor::updateOrCreate(
        [
            'sensor_uid' => $data['sensor_uid'],
            'device_id'  => (string) $device->_id, // clave única combinada
        ],
        [
            'tipo' => $data['tipo']
        ]
    );

    // Guardar la lectura, también asociada al device_id
        SensorData::create([
        'sensor_id'  => $sensor->_id,
        'device_id'  => $sensor->device_id,
        'valor'      => $data['valor'],
        'timestamp'  => $data['timestamp'],
    ]);


    return response()->json(['success' => true]);
}

}
