<?php

namespace App\Models\V2\Projects;

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
use App\Models\V2\AuditableModel;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\MediaModel;
use App\Models\V2\Organisation;
use App\Models\V2\Polygon;
use App\Models\V2\ReportModel;
use App\Models\V2\Seeding;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\User;
use App\Models\V2\Workdays\Workday;
use App\Models\V2\Workdays\WorkdayDemographic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

class ProjectReport extends Model implements MediaModel, AuditableContract, ReportModel, AuditableModel
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

    public $table = 'v2_project_reports';

    public $shortName = 'project-report';

    protected $fillable = [
        'created_by',
        'approved_by',
        'framework_key',
        'old_model',
        'old_id',
        'project_id',
        'task_id',
        'due_at',
        'status',
        'update_request_status',
        'completion',
        'planted_trees',
        'title',
        'technical_narrative',
        'public_narrative',
        'landscape_community_contribution',
        'top_three_successes',
        'challenges_faced',
        'lessons_learned',
        'maintenance_and_monitoring_activities',
        'significant_change',
        'pct_survival_to_date',
        'survival_calculation',
        'survival_comparison',
        'ft_women',
        'ft_men',
        'ft_youth',
        'ft_smallholder_farmers',
        'ft_total',
        'pt_women',
        'pt_men',
        'pt_youth',
        'pt_non_youth',
        'pt_smallholder_farmers',
        'pt_total',
        'seasonal_women',
        'seasonal_men',
        'seasonal_youth',
        'seasonal_smallholder_farmers',
        'seasonal_total',
        'volunteer_women',
        'volunteer_men',
        'volunteer_youth',
        'volunteer_smallholder_farmers',
        'volunteer_total',
        'shared_drive_link',
        'new_jobs_created',
        'new_jobs_description',
        'new_volunteers',
        'volunteers_work_description',
        'ft_jobs_non_youth',
        'ft_jobs_youth',
        'volunteer_non_youth',
        'beneficiaries',
        'beneficiaries_description',
        'beneficiaries_women',
        'beneficiaries_men',
        'beneficiaries_non_youth',
        'beneficiaries_youth',
        'beneficiaries_smallholder',
        'beneficiaries_large_scale',
        'beneficiaries_income_increase',
        'beneficiaries_income_increase_description',
        'beneficiaries_skills_knowledge_increase',
        'beneficiaries_skills_knowledge_increase_description',
        'people_knowledge_skills_increased',
        'feedback',
        'feedback_fields',
        'community_progress',
        'answers',
        'submitted_at',
        'equitable_opportunities',
        'local_engagement',
        'site_addition',
        'paid_other_activity_description',
        'local_engagement_description',
        'indirect_beneficiaries',
        'indirect_beneficiaries_description',

        // virtual (see HasWorkdays trait)
        'other_workdays_description',
    ];

    public $casts = [
        'submitted_at' => 'datetime',
        'due_at' => 'datetime',
        'answers' => 'array',
        'site_addition' => 'boolean',
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
    ];

    // Required by the HasWorkdays trait
    public const WORKDAY_COLLECTIONS = [
        'paid' => [
            Workday::COLLECTION_PROJECT_PAID_NURSERY_OPERATIONS,
            Workday::COLLECTION_PROJECT_PAID_PROJECT_MANAGEMENT,
            Workday::COLLECTION_PROJECT_PAID_OTHER,
        ],
        'volunteer' => [
            Workday::COLLECTION_PROJECT_VOLUNTEER_NURSERY_OPERATIONS,
            Workday::COLLECTION_PROJECT_VOLUNTEER_PROJECT_MANAGEMENT,
            Workday::COLLECTION_PROJECT_VOLUNTEER_OTHER,
        ],
        'other' => [
            Workday::COLLECTION_PROJECT_PAID_OTHER,
            Workday::COLLECTION_PROJECT_VOLUNTEER_OTHER,
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

    public function toSearchableArray()
    {
        return [
            'project_name' => $this->project->name,
            'organisation_name' => $this->organisation->name,
        ];
    }

    /** RELATIONS */
    public function framework(): BelongsTo
    {
        return $this->belongsTo(Framework::class,  'framework_key', 'slug');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function organisation(): BelongsToThrough
    {
        return $this->belongsToThrough(
            Organisation::class,
            Project::class,
            foreignKeyLookup: [Project::class => 'project_id']
        );
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function polygons()
    {
        return $this->morphMany(Polygon::class, 'polygonable');
    }

    public function treeSpecies()
    {
        return $this->morphMany(TreeSpecies::class, 'speciesable');
    }

    /** Calculated Values */
    public function getTaskUuidAttribute(): ?string
    {
        return $this->task?->uuid ?? null;
    }

    public function getFrameworkUuidAttribute(): ?string
    {
        return $this->framework ? $this->framework->uuid : null;
    }

    public function getReportTitleAttribute(): string
    {
        if ($this->framework_key == 'ppc' || empty($this->due_at)) {
            return data_get($this, 'title', '');
        }
        $date = clone $this->due_at->subMonths(1);

        $wEnd = $date->format('F Y');

        $date->subMonths(5);
        $wStart = $date->format('F');

        return "Project Report for $wStart - $wEnd";
    }

    public function getSeedlingsGrownAttribute(): int
    {
        if ($this->framework_key == 'ppc') {
            return $this->treeSpecies()->sum('amount');
        }

        if ($this->framework_key == 'terrafund') {
            if (empty($this->task_id)) {
                return 0;
            }

            return $this->task->nurseryReports()->sum('seedlings_young_trees');
        }

        return 0;
    }

    public function getSeedlingsGrownToDateAttribute(): int
    {
        if ($this->framework_key == 'ppc') {
            return TreeSpecies::where('speciesable_type', ProjectReport::class)
                ->whereIn(
                    'speciesable_id',
                    $this->project->reports()->where('created_at', '<=', $this->created_at)->select('id')
                )
                ->sum('amount');
        }

        // this attribute is currently only used for PPC report exports.
        return 0;
    }

    public function getTreesPlantedCountAttribute(): int
    {
        if (empty($this->task_id)) {
            return 0;
        }

        return TreeSpecies::where('speciesable_type', SiteReport::class)
            ->whereIn('speciesable_id', $this->task->siteReports()->select('id'))
            ->where('collection', TreeSpecies::COLLECTION_PLANTED)
            ->sum('amount');
    }

    public function getSeedsPlantedCountAttribute(): int
    {
        if (empty($this->task_id)) {
            return 0;
        }

        return Seeding::where('seedable_type', SiteReport::class)
            ->whereIn('seedable_id', $this->task->siteReports()->select('id'))
            ->sum('amount');
    }

    public function getTotalJobsCreatedAttribute(): int
    {
        $ptTotal = $this->pt_total ?? 0;
        $ftTotal = $this->ft_total ?? 0;

        return $ftTotal + $ptTotal;
    }

    public function getWorkdaysTotalAttribute(): int
    {
        $projectReportTotal = $this->workdays_paid + $this->workdays_volunteer;

        if (empty($this->task_id)) {
            return $projectReportTotal;
        }

        // Assume that the types are balanced and just return the value from 'gender'
        $sumTotals = fn ($collectionType) => WorkdayDemographic::whereIn(
            'workday_id',
            Workday::where('workdayable_type', SiteReport::class)
                ->whereIn('workdayable_id', $this->task->siteReports()->hasBeenSubmitted()->select('id'))
                ->collections(SiteReport::WORKDAY_COLLECTIONS[$collectionType])
                ->select('id')
        )->gender()->sum('amount');

        return $projectReportTotal + $sumTotals('paid') + $sumTotals('volunteer');
    }

    public function getSiteReportsCountAttribute(): int
    {
        return $this->task?->siteReports()->count() ?? 0;
    }

    public function getNurseryReportsCountAttribute(): ?int
    {
        return $this->task?->nurseryReports()->count() ?? 0;
    }

    public function scopeProjectUuid(Builder $query, string $projectUuid): Builder
    {
        return $query->whereHas('project', function ($qry) use ($projectUuid) {
            $qry->where('uuid', $projectUuid);
        });
    }

    public function scopeCountry(Builder $query, string $country): Builder
    {
        return $query->whereHas('project', function ($qry) use ($country) {
            $qry->where('country', $country);
        });
    }

    public function scopeParentId(Builder $query, string $id): Builder
    {
        return $query->where('project_id', $id);
    }

    public function parentEntity(): BelongsTo
    {
        return $this->project();
    }

    public function auditStatuses(): MorphMany
    {
        return $this->morphMany(AuditStatus::class, 'auditable');
    }

    public function getAuditableNameAttribute(): string
    {
        return $this->title ?? '';
    }
}
