<?php

namespace App\Models\V2\Tasks;

use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasStatus;

    public $table = 'v2_tasks';

    public const STATUS_DUE = 'due';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_COMPLETE = 'complete';

    public static $statuses = [
        self::STATUS_DUE => 'Due',
        self::STATUS_OVERDUE => 'Overdue',
        self::STATUS_COMPLETE => 'Complete',
    ];

    protected $fillable = [
        'organisation_id',
        'project_id',
        'title',
        'status',
        'period_key',
        'due_at',
    ];

    public $casts = [
        'due_at' => 'datetime',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projectReport(): HasOne
    {
        return $this->hasOne(ProjectReport::class);
    }

    public function siteReports(): HasMany
    {
        return $this->hasMany(SiteReport::class);
    }

    public function nurseryReports(): HasMany
    {
        return $this->hasMany(NurseryReport::class);
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function getCompletionStatusAttribute(): string
    {
        if (empty($this->project)) {
            return '';
        }

        $projectCompletion = $this->projectReport()->sum('completion');
        $siteCompletion = $this->siteReports()->sum('completion');
        $nurseryCompletion = $this->nurseryReports()->sum('completion');

        if ($projectCompletion + $siteCompletion + $nurseryCompletion == 0) {
            return 'not-started';
        } else {
            return 'started';
        }
    }
}
