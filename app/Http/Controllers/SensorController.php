<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sensor;

class SensorController extends Controller
{
    public function index()
    {
        $sensores = Sensor::with('data')->get();
        return view('sensors.index', compact('sensores'));
    }
}