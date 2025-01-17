<?php

namespace App\Models\V2\Nurseries;

use App\Models\Framework;
use App\Models\Traits\HasEntityResources;
use App\Models\Traits\HasFrameworkKey;
use App\Models\Traits\HasLinkedFields;
use App\Models\Traits\HasReportStatus;
use App\Models\Traits\HasUpdateRequests;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use App\Models\V2\AuditableModel;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\MediaModel;
use App\Models\V2\Organisation;
use App\Models\V2\Polygon;
use App\Models\V2\Projects\Project;
use App\Models\V2\ReportModel;
use App\Models\V2\Tasks\Task;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\User;
use App\Models\V2\Workdays\Workday;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

class NurseryReport extends Model implements MediaModel, AuditableContract, ReportModel, AuditableModel
{
    use HasFrameworkKey;
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use HasReportStatus;
    use HasLinkedFields;
    use UsesLinkedFields;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use Auditable;
    use HasUpdateRequests;
    use HasEntityResources;
    use BelongsToThroughTrait;

    protected $auditInclude = [
        'status',
        'feedback',
        'feedback_fields',
    ];

    public $table = 'v2_nursery_reports';

    public $shortName = 'nursery-report';

    protected $fillable = [
        'framework_key',
        'nursery_id',
        'task_id',
        'due_at',
        'status',
        'update_request_status',
        'submitted_at',
        'nothing_to_report',
        'completion',
        'title',
        'seedlings_young_trees',
        'interesting_facts',
        'site_prep',
        'shared_drive_link',
        'created_by',
        'approved_by',
        'old_id',
        'old_model',
        'feedback',
        'feedback_fields',
        'answers',
    ];

    public $casts = [
        'due_at' => 'datetime',
        'submitted_at' => 'datetime',
        'nothing_to_report' => 'boolean',
        'answers' => 'array',
    ];

    public $fileConfiguration = [
        'file' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'other_additional_documents' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'tree_seedling_contributions' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'photos' => [
            'validation' => 'photos',
            'multiple' => true,
        ],
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(350)
            ->height(211)
            ->nonQueued();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /** RELATIONS */
    public function framework(): BelongsTo
    {
        return $this->belongsTo(Framework::class, 'framework_key', 'slug');
    }

    public function polygons()
    {
        return $this->morphMany(Polygon::class, 'polygonable');
    }

    public function treeSpecies()
    {
        return $this->morphMany(TreeSpecies::class, 'speciesable')->where('collection', 'nursery-seedling');
    }

    public function nursery(): BelongsTo
    {
        return $this->belongsTo(Nursery::class);
    }

    public function project(): BelongsToThrough
    {
        return $this->belongsToThrough(
            Project::class,
            Nursery::class,
            foreignKeyLookup: [Project::class => 'project_id', Nursery::class => 'nursery_id']
        );
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function organisation(): BelongsToThrough
    {
        return $this->belongsToThrough(
            Organisation::class,
            [Project::class, Nursery::class],
            foreignKeyLookup: [Project::class => 'project_id', Nursery::class => 'nursery_id']
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workdays()
    {
        return $this->morphMany(Workday::class, 'workdayable');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function toSearchableArray()
    {
        return [
            'project_name' => $this->project?->name,
            'organisation_name' => $this->organisation?->name,
        ];
    }

    public static function search($query)
    {
        return self::select('v2_nursery_reports.*')
            ->join('v2_nurseries', 'v2_nursery_reports.project_id', '=', 'v2_nurseries.id')
            ->join('v2_projects', 'v2_nurseries.project_id', '=', 'v2_projects.id')
            ->join('organisations', 'v2_projects.organisation_id', '=', 'organisations.id')
            ->where('v2_projects.name', 'like', "%$query%")
            ->orWhere('organisations.name', 'like', "%$query%");
    }

    public function getReportTitleAttribute(): string
    {
        if (empty($this->due_at)) {
            return data_get($this, 'title', '');
        }

        $date = clone $this->due_at->subMonths(1);
        $wEnd = $date->format('F Y');

        $date->subMonths(5);
        $wStart = $date->format('F');

        return "Nursery Report for $wStart - $wEnd";
    }

    public function getTaskUuidAttribute(): ?string
    {
        return $this->task?->uuid ?? null;
    }

    public function getFrameworkUuidAttribute(): ?string
    {
        return $this->framework ? $this->framework->uuid : null;
    }

    public function getOrganisationAttribute()
    {
        return $this->nursery ? $this->nursery->organisation : null;
    }

    public function scopeProjectUuid(Builder $query, string $projectUuid): Builder
    {
        return $query->whereHas('nursery', function ($qry) use ($projectUuid) {
            $qry->whereHas('project', function ($qry) use ($projectUuid) {
                $qry->where('uuid', $projectUuid);
            });
        });
    }

    public function scopeNurseryUuid(Builder $query, string $nurseryUuid): Builder
    {
        return $query->whereHas('nursery', function ($qry) use ($nurseryUuid) {
            $qry->where('uuid', $nurseryUuid);
        });
    }

    public function scopeParentId(Builder $query, string $id): Builder
    {
        return $query->where('nursery_id', $id);
    }

    public function scopeCountry(Builder $query, string $country): Builder
    {
        return $query->whereHas('nursery', function ($qry) use ($country) {
            $qry->whereHas('project', function ($qry) use ($country) {
                $qry->where('country', $country);
            });
        });
    }

    public function scopeOrganisationUuid(Builder $query, string $organizationUuid): Builder
    {
        return $query->whereHas('organisation', function ($qry) use ($organizationUuid) {
            $qry->where('organisations.uuid', $organizationUuid);
        });
    }

    public function supportsNothingToReport(): bool
    {
        return true;
    }

    public function parentEntity(): BelongsTo
    {
        return $this->nursery();
    }

    public function auditStatuses(): MorphMany
    {
        return $this->morphMany(AuditStatus::class, 'auditable');
    }

    public function getAuditableNameAttribute(): string
    {
        return $this->title ?? '';
    }

    public function getParentNameAttribute(): string
    {
        return $this->nursery?->name ?? '';
    }
}
