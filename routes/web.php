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
use App\Http\Controllers\SensorInfoController;
use App\Exports\SensorDataExport;
use Maatwebsite\Excel\Facades\Excel;

// -------------------- Rutas públicas --------------------

// Registro en 3 pasos: email, código, perfil
Route::get('register', [RegisterController::class, 'showEmailForm'])->name('register');  // Paso 1
Route::post('register/send-code', [RegisterController::class, 'sendVerificationCode'])->name('register.sendCode'); // Enviar código

Route::get('register/verify-code', [RegisterController::class, 'showVerifyCodeForm'])->name('register.verifyCode'); // Paso 2
Route::post('register/verify-code', [RegisterController::class, 'verifyCode'])->name('register.verifyCode.post');

Route::get('register/profile', [RegisterController::class, 'showProfileForm'])->name('register.profile'); // Paso 3
Route::post('register/profile', [RegisterController::class, 'saveProfile'])->name('register.profile.post');

// Login / Logout
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Página pública
Route::view('/about', 'about')->name('about');

// Endpoint para ESP32
//Route::post('/api/sensor-data', [SensorDataController::class, 'store']);

// AJAX para charts
Route::get('/sensor/{sensor_uid}/data', [SensorController::class, 'ajaxData'])->name('sensor.data.ajax');

// Exportaciones
Route::get('/export/sensor/{sensor}', function($sensorId) {
    $fileName = "sensor_{$sensorId}_data.xlsx";
    return Excel::download(new SensorDataExport($sensorId), $fileName);
})->name('export.sensor');

Route::get('/export/device/{esp32_id}', function($esp32Id) {
    $fileName = "device_{$esp32Id}_data.xlsx";
    return Excel::download(new \App\Exports\DeviceSensorsExport($esp32Id), $fileName);
})->name('export.device')->middleware('auth');

// Guía IoT unificada
Route::get('/ejemplos', [SensorInfoController::class, 'catalogo'])->name('ejemplos.index');

// CRUD guías (admin)
Route::resource('sensores-info', SensorInfoController::class)
    ->only(['index', 'store', 'update', 'destroy']);

// -------------------- Rutas protegidas --------------------
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Dispositivos
    Route::get('/devices', [DeviceController::class, 'index'])->name('devices.index');
    Route::get('/devices/create', [DeviceController::class, 'create'])->name('devices.create');
    Route::post('/devices', [DeviceController::class, 'store'])->name('devices.store');
    Route::resource('devices', DeviceController::class)->except(['show', 'edit', 'update']);

    // Perfil
    Route::get('/perfil/edit', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::put('/perfil/update', [PerfilController::class, 'update'])->name('perfil.update');

    // Sensores activos del usuario
    Route::get('/sensors', [SensorDashboardController::class, 'index'])->name('sensors.index');

    // CRUD para admin (catálogo de sensores)
    Route::resource('sensores-info', SensorInfoController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});
