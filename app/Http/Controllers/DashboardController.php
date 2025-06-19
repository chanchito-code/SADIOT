<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Device;

class DashboardController extends Controller
{
    public function index()
    {
        // Traemos los dispositivos del usuario con sensores y las últimas 10 lecturas ordenadas ASC (más antiguas primero)
        $devices = Device::where('user_id', Auth::id())
            ->with(['sensors.data' => function($query) {
                $query->orderBy('timestamp', 'asc')->take(10);
            }])
            ->get();

        // Contamos sensores por tipo para una posible gráfica
        $sensorTypesCount = [];
        foreach ($devices as $device) {
            foreach ($device->sensors as $sensor) {
                $type = $sensor->tipo;
                if (!isset($sensorTypesCount[$type])) {
                    $sensorTypesCount[$type] = 0;
                }
                $sensorTypesCount[$type]++;
            }
        }

        return view('dashboard.index', compact('devices', 'sensorTypesCount'));
    }
}
