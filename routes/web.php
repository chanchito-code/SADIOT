<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\SensorDashboardController;
use App\Http\Controllers\SensorDataController;
use App\Http\Controllers\DashboardController;
use App\Exports\SensorDataExport;
use Maatwebsite\Excel\Facades\Excel;


// ---------------------------------------------
// 🟢 Rutas públicas
// ---------------------------------------------

// Registro y login
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Página pública
Route::view('/about', 'about')->name('about');

// Endpoint público para la ESP32
Route::post('/api/sensor-data', [SensorDataController::class, 'store']);

// ajax
Route::get('/sensor/{sensor_uid}/data', [SensorController::class, 'ajaxData'])->name('sensor.data.ajax');


// Excel
Route::get('/export/sensor/{sensor}', function($sensorId) {
    $fileName = "sensor_{$sensorId}_data.xlsx";
    return Excel::download(new SensorDataExport($sensorId), $fileName);
})->name('export.sensor');

Route::get('/export/device/{esp32_id}', function($esp32Id) {
    $fileName = "device_{$esp32Id}_data.xlsx";
    return Excel::download(new \App\Exports\DeviceSensorsExport($esp32Id), $fileName);
})->name('export.device')->middleware('auth');


// ---------------------------------------------
// 🔒 Rutas protegidas (requieren autenticación)
// ---------------------------------------------
Route::middleware('auth')->group(function () {

    // ✅ Ruta principal del dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 📦 CRUD de dispositivos (ESP32)
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::get('/devices/create', [DeviceController::class, 'create'])->name('devices.create');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');

    // 👤 Perfil de usuario
    Route::get('/perfil/edit', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil/update', [PerfilController::class, 'update'])->name('perfil.update');

    // 📊 Vista de sensores
    Route::get('/sensors', [SensorDashboardController::class, 'index'])->name('sensors.index');

    Route::resource('devices', DeviceController::class)->except(['show','edit','update']);
});
