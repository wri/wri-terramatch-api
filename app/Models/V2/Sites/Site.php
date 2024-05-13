<?php

namespace App\Models\V2\Sites;

use App\Models\Framework;
use App\Models\Traits\HasEntityResources;
use App\Models\Traits\HasEntityStatus;
use App\Models\Traits\HasFrameworkKey;
use App\Models\Traits\HasLinkedFields;
use App\Models\Traits\HasUpdateRequests;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use App\Models\V2\Disturbance;
use App\Models\V2\EntityModel;
use App\Models\V2\Invasive;
use App\Models\V2\MediaModel;
use App\Models\V2\Polygon;
use App\Models\V2\Projects\Project;
use App\Models\V2\Seeding;
use App\Models\V2\Stratas\Strata;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property string project_id
 */
class Site extends Model implements MediaModel, AuditableContract, EntityModel
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use Searchable;
    use HasLinkedFields;
    use UsesLinkedFields;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use HasFrameworkKey;
    use Auditable;
    use HasUpdateRequests;
    use HasEntityStatus;
    use HasEntityResources;

    protected $auditInclude = [
        'status',
        'feedback',
        'feedback_fields',
    ];

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'control_site',
        'boundary_geojson',
        'history',
        'start_date',
        'end_date',
        'land_tenures',
        'status',
        'update_request_status',
        'survival_rate_planted',
        'direct_seeding_survival_rate',
        'a_nat_regeneration_trees_per_hectare',
        'a_nat_regeneration',
        'hectares_to_restore_goal',
        'landscape_community_contribution',
        'technical_narrative',
        'planting_pattern',
        'soil_condition',
        'aim_year_five_crown_cover',
        'aim_number_of_mature_trees',
        'land_use_types',
        'restoration_strategy',
        'siting_strategy',
        'description_siting_strategy',
        'framework_key',
        'old_id',
        'old_model',
        'feedback',
        'feedback_fields',
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
        'tree_species' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'document_files' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'stratification_for_heterogeneity' => [
            'validation' => 'general-documents',
            'multiple' => false,
        ],
    ];

    public $table = 'v2_sites';

    public $shortName = 'site';

    public $casts = [
        'land_tenures' => 'array',
        'land_use_types' => 'array',
        'restoration_strategy' => 'array',
        'answers' => 'array',
        'control_site' => 'boolean',
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

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'project_name' => data_get($this->project, 'name'),
            'organisation_name' => data_get($this->organisation, 'name'),
        ];
    }

    /** RELATIONS */
    public function framework(): BelongsTo
    {
        return $this->belongsTo(Framework::class, 'framework_key', 'slug');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function organisation(): BelongsTo
    {
        return  empty($this->project) ? $this->project : $this->project->organisation();
    }

    public function users(): BelongsToMany
    {
        return  empty($this->project) ? $this->project : $this->project->users();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(SiteReport::class);
    }

    public function monitoring(): HasMany
    {
        return $this->HasMany(SiteMonitoring::class)
            ->isStatus(SiteMonitoring::STATUS_ACTIVE);
    }

    public function monitoringHistoric(): HasMany
    {
        return $this->HasMany(SiteMonitoring::class)
            ->isStatus(SiteMonitoring::STATUS_ARCHIVED);
    }

    public function stratas()
    {
        return $this->morphMany(Strata::class, 'stratasable');
    }

    // @deprecated
    public function polygons()
    {
        return $this->morphMany(Polygon::class, 'polygonable');
    }

    public function sitePolygons()
    {
        return $this->hasMany(SitePolygon::class, 'site_id', 'uuid');
    }

    public function treeSpecies()
    {
        return $this->morphMany(TreeSpecies::class, 'speciesable')->where('collection', 'tree-planted');
    }

    public function nonTreeSpecies()
    {
        return $this->morphMany(TreeSpecies::class, 'speciesable')->where('collection', 'non-tree');
    }

    public function disturbances()
    {
        return $this->morphMany(Disturbance::class, 'disturbanceable');
    }

    public function invasives()
    {
        return $this->morphMany(Invasive::class, 'invasiveable');
    }

    public function seedings(): MorphMany
    {
        return $this->morphMany(Seeding::class, 'seedable');
    }

    /** CALCULATED VALUES */
    public function getHasMonitoringDataAttribute(): int
    {
        return $this->monitoring()->count() > 0 ? 1 : 0;
    }

    public function getSeedsPlantedCountAttribute(): int
    {
        $submissionsIds = SiteReport::where('site_id',  $this->id)->pluck('id')->toArray();

        return Seeding::where('seedable_type', SiteReport::class)
            ->whereIn('seedable_id', $submissionsIds)
            ->sum('amount');
    }

    public function getOrganisationAttribute()
    {
        return $this->project ? $this->project->organisation : null;
    }

    public function getSiteReportsTotalAttribute(): int
    {
        return $this->reports()->count();
    }

    public function getOverdueSiteReportsTotalAttribute(): int
    {
        return $this->reports()
            ->whereIn(
                'status',
                [ReportStatusStateMachine::STARTED, ReportStatusStateMachine::NEEDS_MORE_INFORMATION]
            )
            ->where('due_at', '<', now())
            ->count();
    }

    public function getTreesPlantedCountAttribute(): int
    {
        $reportIds = $this->reports()->pluck('id')->toArray();

        return TreeSpecies::where('speciesable_type', SiteReport::class)
            ->whereIn('speciesable_id', $reportIds)
            ->where('collection', TreeSpecies::COLLECTION_PLANTED)
            ->sum('amount');
    }

    public function getRegeneratedTreesCountAttribute(): int
    {
        if (empty($this->a_nat_regeneration) || empty($this->a_nat_regeneration_trees_per_hectare)) {
            return 0;
        } else {
            return $this->a_nat_regeneration * $this->a_nat_regeneration_trees_per_hectare;
        }
    }

    public function getWorkdayCountAttribute(): int
    {
        $totals = $this->reports()->hasBeenSubmitted()->get([
            DB::raw('sum(`workdays_volunteer`) as volunteer'),
            DB::raw('sum(`workdays_paid`) as paid'),
        ])->first();

        return $totals?->paid + $totals?->volunteer;
    }

    public function getFrameworkUuidAttribute(): ?string
    {
        return $this->framework ? $this->framework->uuid : null;
    }

    public function scopeCountry(Builder $query, $country): Builder
    {
        return $query->whereHas('project', function ($qry) use ($country) {
            $qry->where('country', $country);
        });
    }

    public function scopeOrganisation(Builder $query, $organisationID): Builder
    {
        return $query->whereHas('project', function ($qry) use ($organisationID) {
            $qry->where('organisation_id', $organisationID);
        });
    }

    public function scopeOrganisationUuid(Builder $query, string $uuid): Builder
    {
        return $query->whereHas('project', function ($query) use ($uuid) {
            $query->whereHas('organisation', function ($query) use ($uuid) {
                $query->where('uuid', $uuid);
            });
        });
    }

    public function scopeProjectUuid(Builder $query, string $uuid): Builder
    {
        return $query->whereHas('project', function ($query) use ($uuid) {
            $query->where('uuid', $uuid);
        });
    }

    public function scopeHasMonitoringData(Builder $query, $hasMonitoringData): Builder
    {
        return $hasMonitoringData
            ? $query->has('monitoring')
            : $query->doesntHave('monitoring');
    }
}
