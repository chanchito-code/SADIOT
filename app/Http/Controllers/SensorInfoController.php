<?php

namespace App\Http\Controllers;

use App\Models\SensorInfo;
use Illuminate\Http\Request;

class SensorInfoController extends Controller
{
    // Vista pública unificada (guía + catálogo + ejemplos)
    public function catalogo()
    {
        $sensores = SensorInfo::all();
        return view('ejemplos.index', compact('sensores'));
    }

    // Área administrativa
    public function index()
    {
        $sensores = SensorInfo::all();
        return view('admin.sensores.index', compact('sensores'));
    }

    public function create()
    {
        return view('admin.sensores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'wiring' => 'nullable|string',
            'code_snippet' => 'required|string',
        ]);

        SensorInfo::create($request->all());

        return redirect()->route('ejemplos.index')->with('success', 'Sensor agregado exitosamente');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'wiring' => 'nullable|string',
            'code_snippet' => 'required|string',
        ]);

        $sensor = SensorInfo::findOrFail($id);
        $sensor->update($request->all());

        return redirect()->route('ejemplos.index')->with('success', 'Sensor actualizado exitosamente');
    }

    // Ya no necesitas esto si todo está unificado:
    // public function show($id) {
    //     $sensor = SensorInfo::findOrFail($id);
    //     return view('ejemplos.ficha', compact('sensor'));
    // }
}
