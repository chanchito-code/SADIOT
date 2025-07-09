<?php
// app/Models/SensorInfo.php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class SensorInfo extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sensor_info';

    protected $fillable = [
        'name',         // e.g. "DHT11"
        'description',  // texto breve
        'wiring',       // diagrama en texto o URL de imagen
        'code_snippet', // plantilla de código con placeholders
    ];
}
