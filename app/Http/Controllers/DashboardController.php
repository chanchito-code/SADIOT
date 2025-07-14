<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Device;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $devices = Device::where('user_id', Auth::id())
            ->with(['sensors.data' => function($query) {
                $query->orderBy('timestamp', 'asc');
            }])
            ->get();

        // Inicializar arrays para agrupación por día y por mes
        $groupedData = [
            'temperatura' => ['daily' => [], 'monthly' => []],
            'humedad' => ['daily' => [], 'monthly' => []],
        ];

        foreach ($devices as $device) {
            foreach ($device->sensors as $sensor) {
                $tipo = strtolower($sensor->tipo);

                foreach ($sensor->data as $registro) {
                    $fechaDia = Carbon::parse($registro->timestamp)->format('Y-m-d');
                    $fechaMes = Carbon::parse($registro->timestamp)->format('Y-m');

                    // Agrupar por día
                    if (!isset($groupedData[$tipo]['daily'][$fechaDia])) {
                        $groupedData[$tipo]['daily'][$fechaDia] = ['total' => 0, 'count' => 0];
                    }
                    $groupedData[$tipo]['daily'][$fechaDia]['total'] += $registro->valor;
                    $groupedData[$tipo]['daily'][$fechaDia]['count']++;

                    // Agrupar por mes
                    if (!isset($groupedData[$tipo]['monthly'][$fechaMes])) {
                        $groupedData[$tipo]['monthly'][$fechaMes] = ['total' => 0, 'count' => 0];
                    }
                    $groupedData[$tipo]['monthly'][$fechaMes]['total'] += $registro->valor;
                    $groupedData[$tipo]['monthly'][$fechaMes]['count']++;
                }
            }
        }

        $globalData = [];
        foreach (['temperatura', 'humedad'] as $tipo) {
            $globalData[$tipo] = [
                'daily' => $this->calculateAverages($groupedData[$tipo]['daily']),
                'monthly' => $this->calculateAverages($groupedData[$tipo]['monthly']),
            ];
        }

        return view('dashboard.index', compact('devices', 'globalData'));
    }

    private function calculateAverages(array $data)
    {
        ksort($data);
        $result = [];

        foreach ($data as $fecha => $info) {
            $result[] = [
                'date' => $fecha,
                'avg' => round($info['total'] / $info['count'], 2),
            ];
        }

        return $result;
    }
}
