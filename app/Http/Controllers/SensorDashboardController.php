<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Device;

class SensorDashboardController extends Controller
{

    public function index()
    {
        // Carga todos los dispositivos del usuario, junto con cada sensor y las 10 lecturas más recientes
        $devices = Device::where('user_id', Auth::id())
            ->with(['sensors.data' => function($q) {
                $q->orderBy('timestamp', 'desc')->take(10);
            }])
            ->get();

        // Pasa la colección de devices a la vista
        return view('sensors.index', compact('devices'));
    }
}
