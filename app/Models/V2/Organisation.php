<?php

namespace App\Models\V2;

use App\Models\Draft;
use App\Models\FilterRecord;
use App\Models\Interest;
use App\Models\OrganisationFile;
use App\Models\OrganisationPhoto;
use App\Models\Programme;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Traits\HasDemographics;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\HasVersions;
use App\Models\Traits\NamedEntityTrait;
use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\Forms\Application;
use App\Models\V2\Projects\Project;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Database\Factories\V2\OrganisationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

class Organisation extends Model implements MediaModel
{
    use HasVersions;
    use NamedEntityTrait;
    use HasFactory;
    use HasUuid;
    use HasStatus;
    use HasTypes;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use SoftDeletes;
    use HasTags;
    use HasDemographics;

    /*  Statuses    */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public static $statuses = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PENDING => 'Pending Approval',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
    ];

    /*  Organisation Types */
    public const TYPE_FOR_PROFIT = 'for-profit-organization';
    public const TYPE_NON_PROFIT = 'non-profit-organization';
    public const TYPE_GOVERNMENT = 'government-agency';

    public static $types = [
        self::TYPE_FOR_PROFIT => 'For-Profit Organization',
        self::TYPE_NON_PROFIT => 'Non-Profit Organization',
        self::TYPE_GOVERNMENT => 'Government Agency',
    ];

    public $table = 'organisations';

    public $guarded = [
        'id', 'uuid',
    ];

    public $fileConfiguration = [
        'logo' => [
            'validation' => 'logo-image',
            'multiple' => false,
        ],
        'cover' => [
            'validation' => 'cover-image',
            'multiple' => false,
        ],
        'reference' => [
            'validation' => 'pdf',
            'multiple' => true,
        ],
        'additional' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'bank_statements' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'previous_annual_reports' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'historic_restoration' => [
            'validation' => 'photos',
            'multiple' => true,
        ],
        'op_budget_1year' => [
            'validation' => 'spreadsheet',
            'multiple' => true,
        ],
        'op_budget_2year' => [
            'validation' => 'spreadsheet',
            'multiple' => true,
        ],
        'op_budget_3year' => [
            'validation' => 'spreadsheet',
            'multiple' => true,
        ],
        'op_budget_last_year' => [
            'validation' => 'spreadsheet',
            'multiple' => true,
        ],
        'op_budget_this_year' => [
            'validation' => 'spreadsheet',
            'multiple' => true,
        ],
        'op_budget_next_year' => [
            'validation' => 'spreadsheet',
            'multiple' => true,
        ],
        'legal_registration' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'avg_tree_survival_rate_proof' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'equity_ownership' => [
            'validation' => 'spreadsheet',
            'multiple' => false,
        ],
        'loan_status' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'restoration_photos' => [
            'validation' => 'photos',
            'multiple' => true,
        ],
        'organisation_file' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'organisation_photo' => [
            'validation' => 'photos',
            'multiple' => true,
        ],
        'startup_recognition_cert' => [
            'validation' => 'documents',
            'multiple' => false,
        ],
        'funding_type_documents' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'consortium_proof' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'consortium_partnership_agreements' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'organogram' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'ownership_documents' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'carbon_credits_proof' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
    ];

    public $casts = [
        'is_test' => 'boolean',
        'private' => 'boolean',
        'founding_date' => 'date',
        'fin_start_month' => 'integer',
        'fin_budget_3year' => 'float',
        'fin_budget_2year' => 'float',
        'fin_budget_1year' => 'float',
        'fin_budget_current_year' => 'float',
        'ha_restored_total' => 'float',
        'ha_restored_3year' => 'float',
        'trees_grown_total' => 'integer',
        'total_employees' => 'integer',
        'relevant_experience_years' => 'integer',
        'countries' => 'array',
        'languages' => 'array',
        'states' => 'array',
        'funding_history' => 'array',
        'loan_status_types' => 'array',
        'land_systems' => 'array',
        'fund_utilisation' => 'array',
        'engagement_farmers' => 'array',
        'engagement_women' => 'array',
        'engagement_youth' => 'array',
        'engagement_non_youth' => 'array',
        'engagement_landless' => 'array',
        'restoration_types_implemented' => 'array',
        'detailed_intervention_types' => 'array',
        'tree_restoration_practices' => 'array',
        'biodiversity_focus' => 'array',
        'global_planning_frameworks' => 'array',
        'environmental_impact' => 'string',
        'socioeconomic_impact' => 'string',
        'growith_stage' => 'string',
        'additional_comments' => 'string',
        'consortium' => 'string',
        'female_youth_leadership_example' => 'string',
        'level_0_past_restoration' => 'array',
        'level_1_past_restoration' => 'array',
        'level_2_past_restoration' => 'array',
        'trees_naturally_regenerated_total' => 'integer',
        'trees_naturally_regenerated_3year' => 'integer',
        'carbon_credits' => 'integer',
        'external_technical_assistance' => 'string',
        'barriers_to_funding' => 'string',
        'capacity_building_support_needed' => 'string',
        'associations_cooperatives' => 'boolean',
        'territories_of_operation' => 'array',
        'decisionmaking_structure_description' => 'string',
        'decisionmaking_structure_individuals_involved' => 'string',
        'average_worker_income' => 'integer',
        'anr_practices_past' => 'array',
        'anr_monitoring_approaches' => 'array',
        'anr_monitoring_approaches_description' => 'string',
        'anr_communication_funders' => 'string',
        'bioeconomy_products' => 'string',
        'bioeconomy_traditional_knowledge' => 'string',
        'bioeconomy_product_processing' => 'string',
        'bioeconomy_buyers' => 'string',
    ];

    // Required by the HasDemographics trait
    public const DEMOGRAPHIC_COLLECTIONS = [
        Demographic::EMPLOYEES_TYPE => [
            'all' => [
                // All is used for migrated old organization data, which didn't disaggregate FT / PT.
                DemographicCollections::ALL,
            ],
            'full-time' => [
                DemographicCollections::FULL_TIME,
                DemographicCollections::FULL_TIME_CLT,
            ],
            'part-time' => [
                DemographicCollections::PART_TIME,
                DemographicCollections::PART_TIME_CLT,
            ],
            'temp' => [
                DemographicCollections::TEMP,
            ],
        ],
        Demographic::ALL_BENEFICIARIES_TYPE => DemographicCollections::ALL,
        Demographic::ASSOCIATES_TYPE => DemographicCollections::ALL,
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

    protected static function newFactory()
    {
        return OrganisationFactory::new();
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
        ];
    }

    public static function search($query)
    {
        return self::select('organisations.*')
            ->where('organisations.name', 'like', "%$query%");
    }

    public function treeSpeciesHistorical(): MorphMany
    {
        return $this->morphMany(TreeSpecies::class, 'speciesable')
            ->where('collection', TreeSpecies::COLLECTION_HISTORICAL);
    }

    public function owners(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'organisation_uuid', 'uuid');
    }

    public function ownershipStake(): HasMany
    {
        return $this->hasMany(OwnershipStake::class, 'organisation_id', 'uuid');
    }

    public function leadershipTeam(): HasMany
    {
        return $this->hasMany(Leaderships::class, 'organisation_id', 'id')
            ->where('collection', Leaderships::COLLECTION_LEADERSHIP_TEAM);
    }

    public function coreTeamLeaders(): HasMany
    {
        return $this->hasMany(Leaderships::class, 'organisation_id', 'id')
            ->where('collection', Leaderships::COLLECTION_CORE_TEAM_LEADERS);
    }

    public function financialCollection(): HasMany
    {
        return $this->hasMany(FinancialIndicators::class, 'organisation_id', 'id');
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('status');
    }

    public function shapefiles(): MorphMany
    {
        return $this->morphMany(Shapefile::class, 'shapefileable');
    }

    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    public function users()
    {
        $owners = $this->owners;
        $partners = $this->partners;

        return $owners->merge($partners);
    }

    public function authorisedUsers()
    {
        $owners = $this->owners;
        $approved = $this->usersApproved;

        return $owners->merge($approved);
    }

    public function usersRequested(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->wherePivot('status', 'requested');
    }

    public function usersApproved(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->wherePivot('status', 'approved');
    }

    public function projectPitches(): HasMany
    {
        return $this->hasMany(ProjectPitch::class, 'organisation_id', 'uuid');
    }

    public function fundingTypes(): HasMany
    {
        return $this->hasMany(FundingType::class, 'organisation_id', 'uuid');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function programmes()
    {
        return $this->hasMany(Programme::class);
    }

    public function drafts()
    {
        return $this->hasMany(Draft::class);
    }

    public function organisationPhotos()
    {
        return $this->hasMany(OrganisationPhoto::class);
    }

    public function organisationFiles()
    {
        return $this->hasMany(OrganisationFile::class);
    }

    public function filterRecords()
    {
        return $this->hasMany(FilterRecord::class);
    }

    public function interests()
    {
        return $this->hasMany(Interest::class);
    }

    public function terrafundProgrammes()
    {
        return $this->hasMany(TerrafundProgramme::class);
    }

    public function scopeIsType($query, $type): Builder
    {
        return $query->where('type', $type);
    }
}
