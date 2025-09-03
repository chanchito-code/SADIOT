<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\SensorData;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DataCutExport;
use App\Exports\DataCutMultiSheetExport;

class DataCutController extends Controller
{
    /** 
     * Muestra el formulario de corte de datos 
     */
    public function index()
    {
        // Solo los devices del usuario logueado
        $devices = Device::where('user_id', Auth::id())->get();
        return view('data_cut.index', compact('devices'));
    }

    /**
     * Procesa la petición y extrae los datos para la placa y fecha seleccionadas
     */
     public function show(Request $request)
    {
        $request->validate([
            'esp32_id' => 'required|exists:devices,esp32_id',
            'date'     => 'required|date|before_or_equal:today',
        ]);

        // Formatear la fecha sin tocar la zona (ya viene YYYY-MM-DD)
        $date = Carbon::createFromFormat('Y-m-d', $request->date, 'America/Cancun')
                      ->format('Y-m-d');

        // Purgar registros >15 días (opcional)
        SensorData::where('timestamp', '<', now()->subDays(15))->delete();

        // Obtener el device
        $device = Device::where('esp32_id', $request->esp32_id)->firstOrFail();
        $deviceId = (string) $device->_id;

        // FILTRAR USANDO EL CAMPO 'dia'
        $datos = SensorData::with('sensor')               // cargar relación sensor
                   ->where('device_id', $deviceId)
                   ->where('dia', $date)
                   ->orderBy('timestamp', 'asc')
                   ->get();

        return view('data_cut.index', [
            'devices'        => Device::where('user_id', Auth::id())->get(),
            'selectedDevice' => $device->esp32_id,
            'selectedDate'   => $date,
            'datos'          => $datos,
        ]);
    }

        public function export(Request $request)
    {
        $request->validate([
            'esp32_id' => 'required|exists:devices,esp32_id',
            'date'     => 'required|date|before_or_equal:today',
        ]);

        // Mismos pasos que en show()
        $date   = Carbon::createFromFormat('Y-m-d', $request->date, 'America/Cancun')
                        ->format('Y-m-d');
        $device = Device::where('esp32_id', $request->esp32_id)->firstOrFail();
        $deviceId = (string) $device->_id;

        $fileName = "corte_{$device->esp32_id}_{$date}.xlsx";

        return Excel::download(
            new DataCutMultiSheetExport($deviceId, $date),
            "corte_{$device->esp32_id}_{$date}.xlsx"
        );

    }





}



