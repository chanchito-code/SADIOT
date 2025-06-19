<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::where('user_id', Auth::id())
                         ->orderBy('created_at','desc')
                         ->paginate(10);
        return view('devices.index', compact('devices'));
    }

    public function create()
    {
        return view('devices.create');
    }

    public function store(Request $req)
    {
        $req->validate([
            'esp32_id' => 'required|string|unique:devices,esp32_id',
            'nombre'   => 'nullable|string|max:255',
        ]);

        Device::create([
            'esp32_id' => $req->esp32_id,
            'nombre'   => $req->nombre,
            'user_id'  => Auth::id(),
        ]);

        return redirect()->route('devices.index')
                         ->with('success','Dispositivo añadido.');
    }

    public function destroy(Device $device)
    {
        //$this->authorize('delete', $device);
        $device->delete();
        return back()->with('success','Dispositivo eliminado.');
    }
}
