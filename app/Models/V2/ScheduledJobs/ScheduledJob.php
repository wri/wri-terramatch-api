<?php

namespace App\Models\V2\ScheduledJobs;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

use Parental\HasChildren;

/**
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

    public function scopeReadyToExecute(Builder $query): Builder
    {
        return $query->where('execution_time', '<=', Carbon::now());
    }

    public function getReadyToExecuteAttribute(): bool
    {
        return Carbon::now()->greaterThan($this->execution_time);
    }

    public function execute(): void
    {
        if (! $this->ready_to_execute) {
            Log::error('Attempted to execute task that is not yet ready [execution_time='. $this->execution_time .']');

            return;
        }

        $this->performJob();
        $this->delete();
    }

    protected function performJob(): void
    {
        // To be implemented by subclasses.
    }
}
