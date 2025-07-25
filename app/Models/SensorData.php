<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class SensorData extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'sensor_data';

    // Incluimos 'dia' en fillable
    protected $fillable = [
        'sensor_id',
        'device_id',
        'timestamp',
        'valor',
        'dia',
    ];

    /**
     * Relación con Sensor.
     */
    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class, 'sensor_id', '_id');
    }

    /**
     * Hook para rellenar 'dia' antes de guardar.
     */
    protected static function booted()
    {
        static::saving(function (SensorData $model) {
            if ($model->timestamp) {
                // timestamp viene como Carbon gracias al driver
                $model->dia = Carbon::parse($model->timestamp)
                                    ->timezone('America/Cancun')
                                    ->format('Y-m-d');
            }
        });
    }
}
