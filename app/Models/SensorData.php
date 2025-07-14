<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SensorData extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sensor_data';
    protected $fillable = ['sensor_id', 'device_id', 'timestamp', 'valor'];

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class, 'sensor_id', '_id');
    }
}
