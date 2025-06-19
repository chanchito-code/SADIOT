<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class SensorData extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sensor_data';
    protected $fillable = ['sensor_uid', 'timestamp', 'valor'];


    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class, 'sensor_uid', 'sensor_uid');
    }



}