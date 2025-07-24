<?php

namespace App\Models\V2\ScheduledJobs;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Parental\HasChildren;

/**
 * Note: This class and its subclasses no longer execute in PHP. That implementation has been moved to v3.
 *
 * @property Carbon $execution_time
 * @property bool $ready_to_execute
 * @property array $task_definition;
 */
class ScheduledJob extends Model
{
    use HasChildren;
    use SoftDeletes;

    protected $fillable = [
        'type',
        'task_definition',
        'execution_time',
    ];

    protected $casts = [
        'task_definition' => 'json',
        'execution_time' => 'date',
    ];
}
