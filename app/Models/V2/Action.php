<?php

namespace App\Models\V2;

use App\Models\Traits\HasStatus;
use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use App\Models\V2\Projects\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Action extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use HasStatus;
    use HasTypes;

    public $table = 'v2_actions';

    protected $fillable = [
        'targetable_type',
        'targetable_id',
        'status',
        'type',
        'subtype',
        'title',
        'sub_title',
        'text',
        'key',
        'organisation_id',
        'project_id',
    ];

    public const STATUS_COMPLETE = 'complete';
    public const STATUS_PENDING = 'pending';

    public static $statuses = [
        self::STATUS_COMPLETE => 'Complete',
        self::STATUS_PENDING => 'Pending',
    ];

    public const TYPE_NOTIFICATION = 'notification';
    public const TYPE_TASK = 'task';

    public static $types = [
        self::TYPE_NOTIFICATION => 'Notification',
        self::TYPE_TASK => 'Task',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /** RELATIONS */
    public function targetable()
    {
        return $this->morphTo();
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function scopeComplete(Builder $query)
    {
        return $query->where('status', Action::STATUS_COMPLETE);
    }

    public function scopePending(Builder $query)
    {
        return $query->where('status', Action::STATUS_PENDING);
    }

    public function scopeForTarget(Builder $query, Model $targetable)
    {
        return $query
            ->where('targetable_type', get_class($targetable))
            ->where('targetable_id', $targetable->id);
    }
}
