<?php

namespace App\Models\V2\BaselineMonitoring;

use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SiteMetric extends Model
{
    use HasFactory;
    use HasUuid;
    use HasStatus;

    public const STATUS_TEMP = 'temp';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    public static $statuses = [
        self::STATUS_TEMP => 'Temporary',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_ARCHIVED => 'Archived',
    ];

    public $table = 'baseline_monitoring_metrics_site';

    protected $fillable = [
        'uuid',
        'status',
        'monitorable_type',
        'monitorable_id',

        'tree_count',
        'tree_cover',
        'field_tree_count',
    ];

    public $casts = [
        'tree_count' => 'float',
        'tree_cover' => 'float',
        'field_tree_count' => 'float',
    ];

    public function monitorable(): MorphTo
    {
        return $this->morphTo();
    }
}
