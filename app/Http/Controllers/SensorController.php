<?php

namespace App\Http\Controllers;

use App\Exports\SensorDataExport;
use App\Models\Sensor;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SensorController extends Controller
{
    public function ajaxData(Request $request, $sensor_uid)
    {
        $sensor = Sensor::where('sensor_uid', $sensor_uid)->firstOrFail();

        $deviceId = $sensor->device->esp32_id ?? 'unknown_device';

        $pageName = 'page_'.$deviceId.'_'.$sensor_uid;

        $datos = $sensor->data()
            ->orderBy('timestamp', 'desc')
            ->paginate(5, ['*'], $pageName);

        if ($request->ajax()) {
            return view('partials.sensor_data_pagination', compact('sensor', 'datos'))->render();
        }

        return view('sensor.show', compact('sensor', 'datos'));
    }

    public function exportarExcel(Sensor $sensor)
    {
        return Excel::download(new SensorDataExport($sensor->_id), 'datos_sensor.xlsx');
    }
}
