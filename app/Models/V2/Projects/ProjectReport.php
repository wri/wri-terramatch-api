<?php

namespace App\Models\V2\Projects;

use App\Models\Framework;
use App\Models\Traits\HasFrameworkKey;
use App\Models\Traits\HasLinkedFields;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Polygon;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\UpdateRequests\ApprovalFlow;
use App\Models\V2\UpdateRequests\UpdateRequest;
use App\Models\V2\User;
use App\Models\V2\Workdays\Workday;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProjectReport extends Model implements HasMedia, AuditableContract, ApprovalFlow
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use Searchable;
    use HasStatus;
    use HasLinkedFields;
    use UsesLinkedFields;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use HasFrameworkKey;
    use Auditable;

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
        'due_at',
        'status',
        'update_request_status',
        'completion',
        'completion_status',
        'planted_trees',
        'title',
        'workdays_paid',
        'workdays_volunteer',
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
        'paid_other_activity_description'
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

    public const STATUS_DUE = 'due';
    public const STATUS_STARTED = 'started';
    public const STATUS_AWAITING_APPROVAL = 'awaiting-approval';
    public const STATUS_NEEDS_MORE_INFORMATION = 'needs-more-information';
    public const STATUS_APPROVED = 'approved';

    public static $statuses = [
        self::STATUS_DUE => 'Due',
        self::STATUS_STARTED => 'Started',
        self::STATUS_AWAITING_APPROVAL => 'Awaiting approval',
        self::STATUS_NEEDS_MORE_INFORMATION => 'Needs more information',
        self::STATUS_APPROVED => 'Approved',
    ];

    public const COMPLETION_STATUS_NOT_STARTED = 'not-started';
    public const COMPLETION_STATUS_STARTED = 'started';
    public const COMPLETION_STATUS_COMPLETE = 'complete';

    public static $completionStatuses = [
        self::COMPLETION_STATUS_NOT_STARTED => 'Not started',
        self::COMPLETION_STATUS_STARTED => 'Started',
        self::COMPLETION_STATUS_COMPLETE => 'Complete',
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

    public function organisation(): BelongsTo
    {
        return empty($this->project) ? $this->project : $this->project->organisation();
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

    public function updateRequests()
    {
        return $this->morphMany(UpdateRequest::class, 'updaterequestable');
    }

    public function treeSpecies()
    {
        return $this->morphMany(TreeSpecies::class, 'speciesable');
    }

    public function workdaysPaidProjectEstablishment()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_PROJECT_PAID_PROJECT_ESTABLISHMENT);
    }

    public function workdaysPaidNurseryOperations()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_PROJECT_PAID_NURSERY_OPRERATIONS);
    }

    public function workdaysPaidProjectManagement()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_PROJECT_PAID_PROJECT_MANAGEMENT);
    }

    public function workdaysPaidSeedCollection()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_PROJECT_PAID_SEED_COLLECTION);
    }

    public function workdaysPaidOtherActivities()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_PROJECT_PAID_OTHER);
    }

    public function workdaysVolunteerProjectEstablishment()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_PROJECT_VOLUNTEER_PROJECT_ESTABLISHMENT);
    }

    public function workdaysVolunteerNurseryOperations()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_PROJECT_VOLUNTEER_NURSERY_OPRERATIONS);
    }

    public function workdaysVolunteerProjectManagement()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_PROJECT_VOLUNTEER_PROJECT_MANAGEMENT);
    }

    public function workdaysVolunteerSeedCollection()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_PROJECT_VOLUNTEER_SEED_COLLECTION);
    }

    public function workdaysVolunteerOtherActivities()
    {
        return $this->morphMany(Workday::class, 'workdayable')->where('collection', Workday::COLLECTION_PROJECT_VOLUNTEER_OTHER);
    }

    /** Calculated Values */
    public function getTaskUuidAttribute(): ?string
    {
        return $this->task->uuid ?? null;
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
            return $this->treeSpecies()
                ->sum('amount');
        }

        if ($this->framework_key == 'terrafund') {
            if (empty($this->due_at)) {
                return 0;
            }

            $month = $this->due_at->month;
            $year = $this->due_at->year;
            $nurseryIds = Nursery::where('project_id', data_get($this->project, 'id'))
                ->where('status', Nursery::STATUS_APPROVED)
                ->pluck('id')
                ->toArray();

            if (count($nurseryIds) > 0) {
                return NurseryReport::whereIn('nursery_id', $nurseryIds)
                    ->whereMonth('due_at', $month)
                    ->whereYear('due_at', $year)
                    ->sum('seedlings_young_trees');
            }
        }

        return 0;
    }

    public function getTreesPlantedCountAttribute(): int
    {
        $total = 0;
        if (empty($this->due_at)) {
            return $total;
        }

        $month = $this->due_at->month;
        $year = $this->due_at->year;
        $siteIds = Site::where('project_id', data_get($this->project, 'id'))
            ->where('status', Site::STATUS_APPROVED)
            ->pluck('id')
            ->toArray();

        if (count($siteIds) > 0) {
            $reports = SiteReport::whereIn('site_id', $siteIds)
                ->whereMonth('due_at', $month)
                ->whereYear('due_at', $year)
                ->get();

            foreach ($reports as $report) {
                $total += $report->treeSpecies()->sum('amount');
            }
        }

        return $total;
    }

    public function getTotalJobsCreatedAttribute(): int
    {
        $ptTotal = $this->pt_total ?? 0;
        $ftTotal = $this->ft_total ?? 0;

        return $ftTotal + $ptTotal;
    }

    public function getWorkdaysTotalAttribute(): int
    {
        $paid = $this->workdays_paid ?? 0;
        $volunteer = $this->workdays_volunteer ?? 0;

        if (empty($this->due_at)) {
            return $paid + $volunteer;
        } else {
            $siteIds = $this->project->sites()->pluck('project_id')->toArray();
            $month = $this->due_at->month;
            $year = $this->due_at->year;

            $sitePaid = SiteReport::whereIn('id', $siteIds)
                ->where('due_at', '<', now())
                ->whereNotIn('status', [SiteReport::STATUS_DUE, SiteReport::STATUS_STARTED])
                ->whereMonth('due_at', $month)
                ->whereYear('due_at', $year)
                ->sum('workdays_paid');

            $siteVolunteer = SiteReport::whereIn('id', $siteIds)
                ->where('due_at', '<', now())
                ->whereNotIn('status', [SiteReport::STATUS_DUE, SiteReport::STATUS_STARTED])
                ->whereMonth('due_at', $month)
                ->whereYear('due_at', $year)
                ->sum('workdays_volunteer');

            return $paid + $volunteer + $sitePaid + $siteVolunteer;
        }
    }

    public function getSiteReportsCountAttribute(): int
    {
        if (empty($this->due_at)) {
            return 0;
        }

        $siteIds = $this->project->sites()->pluck('id')->toArray();

        $month = $this->due_at->month;
        $year = $this->due_at->year;

        return SiteReport::whereIn('site_id', $siteIds)
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->count();
    }

    public function getNurseryReportsCountAttribute(): ?int
    {
        if (empty($this->due_at)) {
            return 0;
        }

        $nurseryIds = $this->project->nurseries()->pluck('id')->toArray();
        $month = $this->due_at->month;
        $year = $this->due_at->year;

        return NurseryReport::whereIn('nursery_id', $nurseryIds)
            ->whereMonth('due_at', $month)
            ->whereYear('due_at', $year)
            ->count();
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

    public function getReadableCompletionStatusAttribute(): ?string
    {
        if (empty($this->completion_status)) {
            return null;
        }

        return data_get(static::$completionStatuses, $this->completion_status, 'Unknown');
    }
}
