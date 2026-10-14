<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MongoDB\Laravel\Eloquent\Model;

/**
 * Persisted result of an AI (or heuristic) telemetry analysis.
 *
 * @property string $status
 * @property string $summary
 * @property string $recommended_action
 * @property array $metrics
 * @property string|null $esp32_id
 */
class AiAnalysis extends Model
{
    public const STATUS_NORMAL = 'NORMAL';

    public const STATUS_WARNING = 'WARNING';

    public const STATUS_CRITICAL = 'CRITICAL';

    protected $connection = 'mongodb';

    protected $collection = 'ai_analyses';

    protected $fillable = [
        'device_id',
        'esp32_id',
        'status',
        'summary',
        'recommended_action',
        'metrics',
        'source',
        'model',
        'readings_count',
        'stored_count',
        'generated_at',
    ];

    protected $casts = [
        'metrics' => 'array',
        'readings_count' => 'integer',
        'stored_count' => 'integer',
        'generated_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id', '_id');
    }
}
