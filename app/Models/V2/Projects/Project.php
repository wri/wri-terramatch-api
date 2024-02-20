<?php

namespace App\Models\V2\Projects;

use App\Models\Framework;
use App\Models\Organisation;
use App\Models\Traits\HasFrameworkKey;
use App\Models\Traits\HasLinkedFields;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use App\Models\User;
use App\Models\V2\Forms\Application;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Polygon;
use App\Models\V2\Seeding;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\UpdateRequests\ApprovalFlow;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Project extends Model implements HasMedia, AuditableContract, ApprovalFlow
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use Searchable;
    use HasStatus;
    use HasFrameworkKey;
    use HasLinkedFields;
    use UsesLinkedFields;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use SoftDeletes;
    use Auditable;

    protected $auditInclude = [
        'status',
        'feedback',
        'feedback_fields',
    ];

    public $table = 'v2_projects';

    public $shortName = 'project';

    protected $fillable = [
        'name',
        'status',
        'update_request_status',
        'project_status',
        'framework_key',
        'organisation_id',
        'application_id',
        'boundary_geojson',
        'country',
        'continent',
        'planting_start_date',
        'planting_end_date',
        'description',
        'budget',
        'history',
        'objectives',
        'environmental_goals',
        'socioeconomic_goals',
        'sdgs_impacted',
        'long_term_growth',
        'community_incentives',
        'jobs_created_goal',
        'total_hectares_restored_goal',
        'trees_grown_goal',
        'survival_rate',
        'year_five_crown_cover',
        'monitored_tree_cover',
        'land_use_types',
        'restoration_strategy',
        'old_model',
        'old_id',
        'feedback',
        'feedback_fields',
        'organization_name',
        'project_county_district',
        'description_of_project_timeline',
        'siting_strategy_description',
        'siting_strategy',
        'landholder_comm_engage',
        'proj_partner_info',
        'proj_success_risks',
        'monitor_eval_plan',
        'seedlings_source',
        'pct_employees_men',
        'pct_employees_women',
        'pct_employees_18to35',
        'pct_employees_older35',
        'proj_beneficiaries',
        'pct_beneficiaries_women',
        'pct_beneficiaries_small',
        'pct_beneficiaries_large',
        'pct_beneficiaries_youth',
        'land_tenure_project_area',
        'answers',
    ];

    public $fileConfiguration = [
        'media' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'socioeconomic_benefits' => [
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
        'document_files' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'programme_submission' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'detailed_project_budget' => [
            'validation' => 'spreadsheet',
            'multiple' => false,
        ],
        'proof_of_land_tenure_mou' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
    ];

    public $casts = [
        'land_tenures' => 'array',
        'land_tenure_project_area' => 'array',
        'land_use_types' => 'array',
        'restoration_strategy' => 'array',
        'sdgs_impacted' => 'array',
        'answers' => 'array',
    ];

    public const STATUS_STARTED = 'started';
    public const STATUS_AWAITING_APPROVAL = 'awaiting-approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_NEEDS_MORE_INFORMATION = 'needs-more-information';

    public static $statuses = [
        self::STATUS_STARTED => 'Started',
        self::STATUS_AWAITING_APPROVAL => 'Awaiting approval',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_NEEDS_MORE_INFORMATION => 'Needs more information',
    ];

    public const PROJECT_STATUS_NEW = 'new_project';
    public const PROJECT_STATUS_EXISTING = 'existing_expansion';

    public static $projectStatuses = [
        self::PROJECT_STATUS_NEW => 'New project',
        self::PROJECT_STATUS_EXISTING => 'Existing expansion',
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
        return $this->belongsTo(Framework::class, 'framework_key', 'slug');
    }

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function updateRequests()
    {
        return $this->morphMany(UpdateRequest::class, 'updaterequestable');
    }

    public function controlSites(): HasMany
    {
        return $this->hasMany(Site::class)->where('control_site', true);
    }

    public function nurseries(): HasMany
    {
        return $this->hasMany(Nursery::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ProjectReport::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function siteReports(): HasManyThrough
    {
        return $this->hasManyThrough(SiteReport::class, Site::class);
    }

    public function nurseryReports(): HasManyThrough
    {
        return $this->hasManyThrough(NurseryReport::class, Nursery::class);
    }

    public function monitoring(): HasMany
    {
        return $this->HasMany(ProjectMonitoring::class)
            ->isStatus(ProjectMonitoring::STATUS_ACTIVE);
    }

    public function invites(): HasMany
    {
        return $this->HasMany(ProjectInvite::class);
    }

    public function monitoringHistoric(): HasMany
    {
        return $this->HasMany(ProjectMonitoring::class)
            ->isStatus(ProjectMonitoring::STATUS_ARCHIVED);
    }

    public function polygons()
    {
        return $this->morphMany(Polygon::class, 'polygonable');
    }

    public function treeSpecies()
    {
        return $this->morphMany(TreeSpecies::class, 'speciesable');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'v2_project_users')->withPivot(['status', 'is_monitoring']);
    }

    public function fundingProgramme(): BelongsTo
    {
        return  empty($this->application) ? $this->application : $this->application->fundingProgramme();
    }

    /** CALCULATED VALUES */
    public function getTotalHectaresRestoredCountAttribute(): ?int
    {
        if (! empty($this->monitoring()->count() > 0)) {
            return data_get($this->monitoring()->first(), 'total_hectares');
        }

        return null;
    }

    public function getTreesRestoredCountAttribute(): int
    {
        $treesPlanted = $this->trees_planted_count;
        $seesPlanted = $this->seeds_planted_count;
        $regeneratedTrees = $this->regenerated_trees_count;

        return $treesPlanted + $seesPlanted + $regeneratedTrees;
    }

    public function getProjectReportsTotalAttribute(): int
    {
        return $this->reports()->count();
    }

    public function getTreesPlantedCountAttribute(): int
    {
        $siteIds = Site::where('project_id', $this->id)
            ->where('status', Site::STATUS_APPROVED)
            ->pluck('id')
            ->toArray();

        $submissionsIds = SiteReport::whereIn('site_id', $siteIds)
            ->whereNotIn('status', [SiteReport::STATUS_DUE, SiteReport::STATUS_STARTED])
            ->pluck('id')
            ->toArray();

        return TreeSpecies::where('speciesable_type', SiteReport::class)
            ->whereIn('speciesable_id', $submissionsIds)
            ->where('collection', TreeSpecies::COLLECTION_PLANTED)
            ->sum('amount');
    }

    public function getSeedsPlantedCountAttribute(): int
    {
        $siteIds = $this->sites()->pluck('id')->toArray();
        $submissionsIds = SiteReport::whereIn('site_id', $siteIds)->pluck('id')->toArray();

        return Seeding::where('seedable_type', SiteReport::class)
            ->whereIn('seedable_id', $submissionsIds)
            ->sum('amount');
    }

    public function getRegeneratedTreesCountAttribute(): int
    {
        $sites = Site::where('project_id', $this->id)->get();
        $total = 0;
        foreach ($sites as $site) {
            $total += $site->regenerated_trees_count;
        }

        return $total;
    }

    public function getWorkdayCountAttribute(): int
    {
        $paid = ProjectReport::where('project_id', $this->id)
            ->where('status', ProjectReport::STATUS_APPROVED)
            ->sum('workdays_paid');

        $volunteer = ProjectReport::where('project_id', $this->id)
            ->where('status', ProjectReport::STATUS_APPROVED)
            ->sum('workdays_volunteer');

        $siteIds = $this->sites()->pluck('id')->toArray();

        $sitePaid = SiteReport::whereIn('id', $siteIds)
            ->where('due_at', '<', now())
            ->whereNotIn('status', [SiteReport::STATUS_DUE, SiteReport::STATUS_STARTED])
            ->sum('workdays_paid');

        $siteVolunteer = SiteReport::whereIn('id', $siteIds)
            ->where('due_at', '<', now())
            ->whereNotIn('status', [SiteReport::STATUS_DUE, SiteReport::STATUS_STARTED])
            ->sum('workdays_volunteer');

        return $paid + $volunteer + $sitePaid + $siteVolunteer;
    }

    public function getTotalJobsCreatedAttribute(): int
    {
        $ftTotal = ProjectReport::where('project_id', $this->id)
            ->sum('ft_total');

        $ptTotal = ProjectReport::where('project_id', $this->id)
            ->sum('pt_total');

        return $ftTotal + $ptTotal;
    }

    public function getTotalSitesAttribute(): int
    {
        return $this->sites()
            ->where('status', Site::STATUS_APPROVED)
            ->count();
    }

    public function getTotalNurseriesAttribute(): int
    {
        return $this->nurseries()
            ->where('status', Nursery::STATUS_APPROVED)
            ->count();
    }

    public function getTotalProjectReportsAttribute(): int
    {
        return $this->reports()->count();
    }

    public function getTotalOverdueReportsAttribute(): int
    {
        $siteIds = $this->sites()->pluck('id')->toArray();
        $nurseryIds = $this->nurseries()->pluck('id')->toArray();

        $pOverdue = $this->reports()
            ->where('due_at', '<', now())
            ->whereIn('status', [ProjectReport::STATUS_DUE, ProjectReport::STATUS_STARTED])
            ->count();

        $sOverdue = SiteReport::whereIn('id', $siteIds)
            ->where('due_at', '<', now())
            ->whereIn('status', [SiteReport::STATUS_DUE, SiteReport::STATUS_STARTED])
            ->count();

        $nOverdue = NurseryReport::whereIn('id', $nurseryIds)
            ->where('due_at', '<', now())
            ->whereIn('status', [NurseryReport::STATUS_DUE, NurseryReport::STATUS_STARTED])
            ->count();

        return $pOverdue + $sOverdue + $nOverdue;
    }

    public function getHasMonitoringDataAttribute(): int
    {
        return $this->monitoring()->count() > 0 ? 1 : 0;
    }

    public function getTotalReportingTasksAttribute(): int
    {
        $siteIds = $this->sites()->pluck('id')->toArray();
        $nurseryIds = $this->nurseries()->pluck('id')->toArray();

        $RawDueDates = ProjectReport::where('project_id', $this->id)
            ->pluck('due_at')
            ->filter()
            ->map(function ($carbonObject) {
                return $carbonObject->format('Y-m-d H:i:s');
            })
            ->toArray();

        $pendingReportingTasks = 0;

        foreach ($RawDueDates as $RawDueDate) {
            $dueDate = Carbon::parse($RawDueDate);

            $projectReportPending = $this->getReportPendingCount(ProjectReport::class, $dueDate, "project_id", $siteIds, $nurseryIds);
            $siteRepPending = $this->getReportPendingCount(SiteReport::class, $dueDate, "site_id", $siteIds, $nurseryIds);
            $nurRepPending = $this->getReportPendingCount(NurseryReport::class, $dueDate, "nursery_id", $siteIds, $nurseryIds);
            // TODO (NJC): Temporary: just getting this code working again since the removal of forProjectAndDate. This
            //  method will be rewritten in TM-560
            $hasPendingTask = Task::where('project_id', $this->id)
                ->whereMonth('due_at', $dueDate->month)
                ->whereYear('due_at', $dueDate->year)
                ->whereNot('status', Task::STATUS_COMPLETE)
                ->exists();

            if ($projectReportPending + $siteRepPending + $nurRepPending > 0 || $hasPendingTask) {
                $pendingReportingTasks++;
            }
        }
        
        return $pendingReportingTasks;
    }
    
    private function getReportPendingCount(string $reportType, Carbon $dueDate, string $idColumn, array $siteIds, array $nurseryIds): int
    {
        $ids = ($reportType === ProjectReport::class) ? [$this->id] : (($reportType === SiteReport::class) ? $siteIds : $nurseryIds);
    
        return $reportType::whereIn($idColumn, $ids)
            ->whereMonth('due_at', $dueDate->month)
            ->whereYear('due_at', $dueDate->year)
            ->whereIn('status', [ProjectReport::STATUS_DUE, ProjectReport::STATUS_STARTED])
            ->count();
    }

    public function getFrameworkUuidAttribute(): ?string
    {
        return $this->framework ? $this->framework->uuid : null;
    }

    public function scopeOrganisationUuid(Builder $query, string $uuid): Builder
    {
        return $query->whereHas('organisation', function ($query) use ($uuid) {
            $query->where('uuid', $uuid);
        });
    }

    public function scopeHasMonitoringData(Builder $query, $hasMonitoringData): Builder
    {
        return $hasMonitoringData
            ? $query->has('monitoring')
            : $query->doesntHave('monitoring');
    }

    /** SEARCH */
    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
        ];
    }
}
