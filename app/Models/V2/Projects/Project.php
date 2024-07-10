<?php

namespace App\Models\V2\Projects;

use App\Models\Framework;
use App\Models\Organisation;
use App\Models\Traits\HasEntityResources;
use App\Models\Traits\HasEntityStatus;
use App\Models\Traits\HasFrameworkKey;
use App\Models\Traits\HasLinkedFields;
use App\Models\Traits\HasUpdateRequests;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use App\Models\User;
use App\Models\V2\AuditableModel;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\EntityModel;
use App\Models\V2\Forms\Application;
use App\Models\V2\MediaModel;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Polygon;
use App\Models\V2\Seeding;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\Workdays\Workday;
use App\Models\V2\Workdays\WorkdayDemographic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Project extends Model implements MediaModel, AuditableContract, EntityModel, AuditableModel
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use Searchable;
    use HasFrameworkKey;
    use HasLinkedFields;
    use UsesLinkedFields;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use SoftDeletes;
    use Auditable;
    use HasUpdateRequests;
    use HasEntityStatus;
    use HasEntityResources;

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
        'lat',
        'long',
        'answers',
        'ppc_external_id',
        'detailed_intervention_types',
        'proj_impact_foodsec',
        'pct_employees_marginalised',
        'pct_beneficiaries_marginalised',
        'pct_beneficiaries_men',
        'proposed_gov_partners',
        'proposed_num_nurseries',
        'proj_boundary',
        'states',
        'proj_impact_biodiv',
        'water_source',
        'baseline_biodiversity',
        'goal_trees_restored_planting',
        'goal_trees_restored_anr',
        'goal_trees_restored_direct_seeding',
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
        'detailed_intervention_types' => 'array',
        'states' => 'array',
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

    // @deprecated
    public function polygons()
    {
        return $this->morphMany(Polygon::class, 'polygonable');
    }

    public function sitePolygons(): HasManyThrough
    {
        return $this->hasManyThrough(
            SitePolygon::class,
            Site::class,
            'project_id',
            'site_id',
            'id',
            'uuid'
        )->active();
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
        return TreeSpecies::where('speciesable_type', SiteReport::class)
            ->whereIn('speciesable_id', $this->submittedSiteReportIds())
            ->where('collection', TreeSpecies::COLLECTION_PLANTED)
            ->sum('amount');
    }

    public function getSeedsPlantedCountAttribute(): int
    {
        return Seeding::where('seedable_type', SiteReport::class)
            ->whereIn('seedable_id', $this->submittedSiteReportIds())
            ->sum('amount');
    }

    public function getRegeneratedTreesCountAttribute(): int
    {
        return $this->submittedSiteReports()->sum('num_trees_regenerating');
    }

    public function getWorkdayCountAttribute(): int
    {
        return WorkdayDemographic::whereIn(
            'workday_id',
            Workday::where('workdayable_type', SiteReport::class)
                ->whereIn('workdayable_id', $this->submittedSiteReports()->select('v2_site_reports.id'))
                ->select('id')
        )->orWhereIn(
            'workday_id',
            Workday::where('workdayable_type', ProjectReport::class)
                ->whereIn('workdayable_id', $this->reports()->hasBeenSubmitted()->select('id'))
                ->select('id')
        )->gender()->sum('amount') ?? 0;
    }

    public function getSelfReportedWorkdayCountAttribute(): int
    {
        $sumQueries = [
            DB::raw('sum(`workdays_paid`) as paid'),
            DB::raw('sum(`workdays_volunteer`) as volunteer'),
        ];
        $projectTotals = $this->reports()->hasBeenSubmitted()->get($sumQueries)->first();
        // The groupBy is superfluous, but required because Laravel adds "v2_sites.project_id as laravel_through_key" to
        // the SQL select.
        $siteTotals = $this->submittedSiteReports()->groupBy('v2_sites.project_id')->get($sumQueries)->first();

        return $projectTotals?->paid + $projectTotals?->volunteer + $siteTotals?->paid + $siteTotals?->volunteer;
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
        return $this->sites()->isApproved()->count();
    }

    public function getTotalNurseriesAttribute(): int
    {
        return $this->nurseries()->isApproved()->count();
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
            ->isIncomplete()
            ->count();

        $sOverdue = SiteReport::whereIn('site_id', $siteIds)
            ->where('due_at', '<', now())
            ->isIncomplete()
            ->count();

        $nOverdue = NurseryReport::whereIn('nursery_id', $nurseryIds)
            ->where('due_at', '<', now())
            ->isIncomplete()
            ->count();

        return $pOverdue + $sOverdue + $nOverdue;
    }

    public function getHasMonitoringDataAttribute(): int
    {
        return $this->monitoring()->count() > 0 ? 1 : 0;
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

    // All Entities are expected to have a project attribute.
    public function getProjectAttribute(): Project
    {
        return $this;
    }

    /** SEARCH */
    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
        ];
    }

    public function auditStatuses(): MorphMany
    {
        return $this->morphMany(AuditStatus::class, 'auditable');
    }

    public function getAuditableNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * @return HasManyThrough A relation for all site reports associated with this project that is for an approved
     *   site, and has a report status past due/started (has been submitted).
     */
    private function submittedSiteReports(): HasManyThrough
    {
        // scopes that use status don't work on the HasManyThrough because both Site and SiteReport have
        // a status field.
        return $this
            ->siteReports()
            ->whereIn('v2_sites.status', Site::$approvedStatuses)
            ->whereNotIn('v2_site_reports.status', SiteReport::UNSUBMITTED_STATUSES);
    }

    /**
     * @return HasManyThrough The query of site report IDs for all reports associated with sites that have been
     * approved, and have a report status not in due or started (reports that have been submitted).
     */
    private function submittedSiteReportIds(): HasManyThrough
    {
        return $this->submittedSiteReports()->select('v2_site_reports.id');
    }

    public function getTotalSitePolygonsAttribute()
    {
        return $this->sitePolygons()->count();
    }
}
