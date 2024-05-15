<?php

namespace App\Models\V2\Sites;

use App\Http\Resources\V2\SiteReports\SiteReportResource;
use App\Models\Framework;
use App\Models\Traits\HasEntityResources;
use App\Models\Traits\HasFrameworkKey;
use App\Models\Traits\HasLinkedFields;
use App\Models\Traits\HasReportStatus;
use App\Models\Traits\HasUpdateRequests;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\HasWorkdays;
use App\Models\Traits\UsesLinkedFields;
use App\Models\V2\Disturbance;
use App\Models\V2\Invasive;
use App\Models\V2\MediaModel;
use App\Models\V2\Organisation;
use App\Models\V2\Polygon;
use App\Models\V2\Projects\Project;
use App\Models\V2\ReportModel;
use App\Models\V2\Seeding;
use App\Models\V2\Tasks\Task;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\User;
use App\Models\V2\Workdays\Workday;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Scout\Searchable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

class SiteReport extends Model implements MediaModel, AuditableContract, ReportModel
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use Searchable;
    use HasReportStatus;
    use HasLinkedFields;
    use UsesLinkedFields;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use HasFrameworkKey;
    use Auditable;
    use HasUpdateRequests;
    use HasEntityResources;
    use BelongsToThroughTrait;
    use HasWorkdays;

    protected $auditInclude = [
        'status',
        'feedback',
        'feedback_fields',
    ];

    protected $fillable = [
        'status',
        'update_request_status',
        'nothing_to_report',
        'approved_at',
        'approved_by',
        'created_by',
        'submitted_at',
        'workdays_paid',
        'workdays_volunteer',
        'technical_narrative',
        'public_narrative',
        'disturbance_details',
        'title',
        'shared_drive_link',
        'framework_key',
        'due_at',
        'completion',
        'seeds_planted',
        'old_id',
        'old_model',
        'site_id',
        'task_id',
        'feedback',
        'feedback_fields',
        'polygon_status',
        'answers',
        'paid_other_activity_description',

        // virtual (see HasWorkdays trait)
        'other_workdays_description',
    ];

    public $fileConfiguration = [
        'socioeconomic_benefits' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'media' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'file' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'other_additional_documents' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'photos' => [
            'validation' => 'photos',
            'multiple' => true,
        ],
        'tree_species' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'site_submission' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'document_files' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
    ];

    public $table = 'v2_site_reports';

    public $shortName = 'site-report';

    public $casts = [
        'due_at' => 'datetime',
        'submitted_at' => 'datetime',
        'nothing_to_report' => 'boolean',
        'answers' => 'array',
    ];

    // Required by the HasWorkdays trait
    public const WORKDAY_COLLECTIONS = Workday::SITE_COLLECTIONS;
    public const OTHER_WORKDAY_COLLECTIONS = [
        Workday::COLLECTION_SITE_PAID_OTHER,
        Workday::COLLECTION_SITE_VOLUNTEER_OTHER,
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(350)
            ->height(211)
            ->nonQueued();
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /** RELATIONS */
    public function framework(): BelongsTo
    {
        return $this->belongsTo(Framework::class,  'framework_key', 'slug');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function project(): BelongsToThrough
    {
        return $this->belongsToThrough(
            Project::class,
            Site::class,
            foreignKeyLookup: [Project::class => 'project_id', Site::class => 'site_id']
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
            [Project::class, Site::class],
            foreignKeyLookup: [Project::class => 'project_id', Site::class => 'site_id']
        );
    }

    public function polygons(): MorphMany
    {
        return $this->morphMany(Polygon::class, 'polygonable');
    }

    public function treeSpecies()
    {
        return $this->morphMany(TreeSpecies::class, 'speciesable')->where('collection', 'tree-planted');
    }

    public function nonTreeSpecies()
    {
        return $this->morphMany(TreeSpecies::class, 'speciesable')->where('collection', 'non-tree');
    }

    public function seedings(): MorphMany
    {
        return $this->morphMany(Seeding::class, 'seedable');
    }

    public function disturbances()
    {
        return $this->morphMany(Disturbance::class, 'disturbanceable');
    }

    public function invasive()
    {
        return $this->morphMany(Invasive::class, 'invasiveable');
    }

    public function approvedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'approved_by');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function getReportTitleAttribute(): string
    {
        if (empty($this->due_at)) {
            return data_get($this, 'title', '');
        }

        $date = clone $this->due_at->subMonths(1);
        $wEnd = $date->format('F Y');

        if ($this->framework_key == 'ppc') {
            $date->subMonths(2);
            $wStart = $date->format('F');
        } else {
            $date->subMonths(5);
            $wStart = $date->format('F');
        }

        return "Site Report for $wStart - $wEnd";
    }

    public function getTotalWorkdaysCountAttribute(): int
    {
        return $this->workdays_paid + $this->workdays_volunteer;
    }

    public function getTotalTreesPlantedCountAttribute(): int
    {
        return $this->treeSpecies()->sum('amount');
    }

    public function getOrganisationAttribute()
    {
        return $this->site ? $this->site->organisation : null;
    }

    public function getTaskUuidAttribute(): ?string
    {
        return $this->task?->uuid ?? null;
    }

    public function toSearchableArray()
    {
        return [
            'project_name' => $this->project?->name,
            'organisation_name' => $this->organisation?->name,
        ];
    }

    public function getFrameworkUuidAttribute(): ?string
    {
        return $this->framework ? $this->framework->uuid : null;
    }

    public function scopeProjectUuid(Builder $query, string $projectUuid): Builder
    {
        return $query->whereHas('site', function ($qry) use ($projectUuid) {
            $qry->whereHas('project', function ($qry) use ($projectUuid) {
                $qry->where('uuid', $projectUuid);
            });
        });
    }

    public function scopeSiteUuid(Builder $query, string $siteUuid): Builder
    {
        return $query->whereHas('site', function ($qry) use ($siteUuid) {
            $qry->where('uuid', $siteUuid);
        });
    }

    public function scopeCountry(Builder $query, string $country): Builder
    {
        return $query->whereHas('site', function ($qry) use ($country) {
            $qry->whereHas('project', function ($qry) use ($country) {
                $qry->where('country', $country);
            });
        });
    }

    public function scopeParentId(Builder $query, string $id): Builder
    {
        return $query->where('site_id', $id);
    }

    public function createResource(): JsonResource
    {
        return new SiteReportResource($this);
    }

    public function supportsNothingToReport(): bool
    {
        return true;
    }

    public function parentEntity(): BelongsTo
    {
        return $this->site();
    }
}
