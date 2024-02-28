<?php

namespace App\Models\V2\Sites;

use App\Http\Resources\V2\SiteReports\SiteReportResource;
use App\Http\Resources\V2\SiteReports\SiteReportWithSchemaResource;
use App\Models\Framework;
use App\Models\Traits\HasForm;
use App\Models\Traits\HasFrameworkKey;
use App\Models\Traits\HasLinkedFields;
use App\Models\Traits\HasReportStatus;
use App\Models\Traits\HasUpdateRequests;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use App\Models\V2\Disturbance;
use App\Models\V2\Invasive;
use App\Models\V2\Polygon;
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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SiteReport extends Model implements HasMedia, AuditableContract, ReportModel
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
    use HasForm;

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
        'paid_other_activity_description'
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

    public function project(): BelongsTo
    {
        return empty($this->site) ? $this->site : $this->site->project();
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function organisation(): BelongsTo
    {
        return  empty($this->project) ? $this->project : $this->project->organisation();
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

    public function workdaysPaidSiteEstablishment()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_SITE_PAID_SITE_ESTABLISHMENT);
    }

    public function workdaysPaidPlanting()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_SITE_PAID_PLANTING);
    }

    public function workdaysPaidSiteMaintenance()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_SITE_PAID_SITE_MAINTENANCE);
    }

    public function workdaysPaidSiteMonitoring()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_SITE_PAID_SITE_MONITORING);
    }

    public function workdaysPaidOtherActivities()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_SITE_PAID_OTHER);
    }

    public function workdaysVolunteerSiteEstablishment()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_SITE_VOLUNTEER_SITE_ESTABLISHMENT);
    }

    public function workdaysVolunteerPlanting()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_SITE_VOLUNTEER_PLANTING);
    }

    public function workdaysVolunteerSiteMaintenance()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_SITE_VOLUNTEER_SITE_MAINTENANCE);
    }

    public function workdaysVolunteerSiteMonitoring()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_SITE_VOLUNTEER_SITE_MONITORING);
    }

    public function workdaysVolunteerOtherActivities()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_SITE_VOLUNTEER_OTHER);
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
        return $this->task->uuid ?? null;
    }

    public function toSearchableArray()
    {
        return [
            'project_name' => $this->site->project->name,
            'organisation_name' => $this->organisation->name,
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

    public function createSchemaResource(): JsonResource
    {
        return new SiteReportWithSchemaResource($this, ['schema' => $this->getForm()]);
    }

    public function supportsNothingToReport(): bool
    {
        return true;
    }

    public function getLinkedFieldsConfig()
    {
        return config('wri.linked-fields.models.site-report.fields', []);
    }
}
