<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;
use App\Models\SensorData;
use App\Models\Device;
use Carbon\Carbon;

class SensorController extends Controller
{
    // Método para paginación AJAX, lo tienes ya
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


    public function datosPorDiaGeneral(Request $request)
    {
        $deviceEsp32Id = $request->query('device');
        if (!$deviceEsp32Id) {
            return redirect()->back()->withErrors(['device' => 'Debe seleccionar un dispositivo.']);
        }

        $device = Device::where('esp32_id', $deviceEsp32Id)->first();
        if (!$device) {
            return redirect()->back()->withErrors(['device' => 'Dispositivo no encontrado.']);
        }

        $sensors = Sensor::where('device_id', $device->_id)->get();
        if ($sensors->isEmpty()) {
            return redirect()->back()->withErrors(['device' => 'El dispositivo no tiene sensores asociados.']);
        }

        $sensorIds = $sensors->pluck('_id')->toArray();

        // Obtener fechas únicas de todos sensores del dispositivo
        $fechasRaw = SensorData::raw(function($collection) use ($sensorIds) {
            return $collection->aggregate([
                ['$match' => ['sensor_id' => ['$in' => $sensorIds]]],
                ['$project' => [
                    'fecha' => [
                        '$dateToString' => [
                            'format' => '%Y-%m-%d',
                            'date' => ['$toDate' => '$timestamp']
                        ]
                    ]
                ]],
                ['$group' => ['_id' => '$fecha']],
                ['$sort' => ['_id' => -1]]
            ]);
        });

        $fechas = collect($fechasRaw)->map(fn($item) => \Carbon\Carbon::parse($item->_id));

        $fechaSeleccionada = $request->input('fecha');

        $query = SensorData::whereIn('sensor_id', $sensorIds);

        if ($fechaSeleccionada) {
            $inicio = \Carbon\Carbon::parse($fechaSeleccionada)->startOfDay();
            $fin = \Carbon\Carbon::parse($fechaSeleccionada)->endOfDay();

            $query->whereBetween('timestamp', [$inicio, $fin]);
        }

        $datos = $query->orderBy('timestamp', 'asc')->paginate(15);



        \Log::info('Dispositivo seleccionado: ' . $deviceEsp32Id);
        \Log::info('Fechas disponibles para el dispositivo: ' . $fechas->map(fn($f) => $f->format('Y-m-d'))->implode(', '));
        \Log::info('Fecha seleccionada: ' . $fechaSeleccionada);
        \Log::info('Sensores del dispositivo: ' . $sensors->pluck('sensor_uid')->implode(', '));
        \Log::info('Total datos encontrados para la fecha: ' . $datos->count());



        return view('sensors.datos_por_dia_general', [
            'deviceId' => $deviceEsp32Id,
            'fechas' => $fechas,
            'datos' => $datos,
            'fechaSeleccionada' => $fechaSeleccionada,
            'sensors' => $sensors,
        ]);
    }

}
