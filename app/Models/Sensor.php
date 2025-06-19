<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Sensor extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sensors';
    protected $fillable = ['sensor_uid', 'tipo', 'meta', 'device_id'];

    public function data()
    {
        return $this->hasMany(SensorData::class, 'sensor_uid', 'sensor_uid');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id', '_id');
    }
}
