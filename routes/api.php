<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorDataController;


Route::post('sensors/data', [SensorDataController::class, 'store']);

