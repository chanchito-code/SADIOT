<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Sensor;
use App\Models\SensorData;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SensorController extends Controller
{
    public function ajaxData(Request $request, $sensor_uid)
    {
        $sensor = Sensor::where('sensor_uid', $sensor_uid)->firstOrFail();

        $deviceId = $sensor->device->esp32_id ?? 'unknown_device';

        $pageName = 'page_' . $deviceId . '_' . $sensor_uid;

        $datos = $sensor->data()
            ->orderBy('timestamp', 'desc')
            ->paginate(5, ['*'], $pageName);

        if ($request->ajax()) {
            return view('partials.sensor_data_pagination', compact('sensor', 'datos'))->render();
        }

        return view('sensor.show', compact('sensor', 'datos'));
    }

    public function corteAutomatico()
    {
        $userId = Auth::id();

        $devices = Device::where('user_id', $userId)->get();

        if ($devices->isEmpty()) {
            return view('sensors.corte_automatico', compact('devices'))
                ->withErrors(['devices' => 'No tienes dispositivos registrados.']);
        }

        $device = $devices->first();
        $sensors = Sensor::where('device_id', $device->_id)->get();
        $sensorIds = $sensors->pluck('_id')->toArray();

        if (empty($sensorIds)) {
            return view('sensors.corte_automatico', compact('devices'))
                ->withErrors(['sensors' => 'El dispositivo no tiene sensores asociados.']);
        }

        $ultimoDato = SensorData::whereIn('sensor_id', $sensorIds)
                    ->orderBy('timestamp', 'desc')
                    ->first();

        if (!$ultimoDato) {
            return view('sensors.corte_automatico', compact('devices'))
                ->withErrors(['datos' => 'No hay datos registrados aún.']);
        }

        // Asegurar que timestamp es un objeto Carbon y no un string
        $timestamp = Carbon::parse($ultimoDato->timestamp);
        $fechaSeleccionada = $timestamp->copy()->timezone('America/Cancun')->format('Y-m-d');

        // Calcular inicio y fin del día en UTC
        $inicioUtc = Carbon::createFromFormat('Y-m-d', $fechaSeleccionada, 'America/Cancun')->startOfDay()->setTimezone('UTC');
        $finUtc    = Carbon::createFromFormat('Y-m-d', $fechaSeleccionada, 'America/Cancun')->endOfDay()->setTimezone('UTC');

        // Buscar datos entre ese rango
        $datos = SensorData::whereIn('sensor_id', $sensorIds)
         ->where('timestamp', 'regex', new \MongoDB\BSON\Regex("^{$fechaSeleccionada}"))
         ->orderBy('timestamp', 'asc')
         ->paginate(15);


        return view('sensors.corte_automatico', compact('devices', 'device', 'sensors', 'datos', 'fechaSeleccionada'));
    }


    public function exportDeviceDate($deviceEsp32Id, $fechaSeleccionada)
    {
        $device    = Device::where('esp32_id', $deviceEsp32Id)->firstOrFail();
        $sensorIds = Sensor::where('device_id', $device->_id)->pluck('_id')->toArray();

        $inicioUtc = Carbon::parse($fechaSeleccionada, 'America/Cancun')->startOfDay()->setTimezone('UTC');
        $finUtc    = Carbon::parse($fechaSeleccionada, 'America/Cancun')->endOfDay()->setTimezone('UTC');

        $datos = SensorData::whereIn('sensor_id', $sensorIds)
                  ->whereBetween('timestamp', [$inicioUtc, $finUtc])
                  ->orderBy('timestamp', 'asc')
                  ->get();

        $response = new StreamedResponse(function() use ($datos) {
            $out = fopen('php://output','w');
            fputcsv($out, ['Sensor UID','Tipo','Fecha UTC','Fecha Local','Valor']);
            foreach ($datos as $dato) {
                $sensor = Sensor::firstWhere('_id', $dato->sensor_id);
                fputcsv($out, [
                    $sensor->sensor_uid,
                    ucfirst($sensor->tipo),
                    $dato->timestamp->format('Y-m-d H:i:s'),
                    $dato->timestamp->timezone('America/Cancun')->format('Y-m-d H:i:s'),
                    $dato->valor ?? $dato->value,
                ]);
            }
            fclose($out);
        });

        $filename = "corte_{$deviceEsp32Id}_{$fechaSeleccionada}.csv";
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename={$filename}");
        return $response;
    }
}
