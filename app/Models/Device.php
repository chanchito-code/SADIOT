<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Device extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'devices';

    protected $fillable = [
        'esp32_id',
        'user_id',
        'nombre', // opcional para que el usuario identifique mejor el dispositivo
    ];

    // Relación con usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', '_id');
    }

    // Relación con sensores (un dispositivo tiene muchos sensores)
    public function sensors()
    {
        return $this->hasMany(Sensor::class, 'device_id', '_id');
    }
}
