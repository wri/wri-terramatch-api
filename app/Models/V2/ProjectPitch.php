<?php

namespace App\Models\V2;

use App\Models\Traits\HasDemographics;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

class ProjectPitch extends Model implements MediaModel
{
    use HasFactory;
    use HasUuid;
    use HasStatus;
    use SoftDeletes;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use HasTags;
    use HasDemographics;

    /*  Statuses    */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';

    public static $statuses = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_ACTIVE => 'Active',
    ];

    protected $guarded = [
        'id', 'uuid',
    ];

    public $casts = [
        'capacity_building_needs' => 'array',
        'restoration_intervention_types' => 'array',
        'sustainable_dev_goals' => 'array',
        'land_tenure_proj_area' => 'array',
        'how_discovered' => 'array',
        'states' => 'array',
        'land_systems' => 'array',
        'tree_restoration_practices' => 'array',
        'detailed_intervention_types' => 'array',
        'land_use_types' => 'array',
        'restoration_strategy' => 'array',
    ];

    public $fileConfiguration = [
        'cover' => [
            'validation' => 'cover-image',
            'multiple' => false,
        ],
        'additional' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'restoration_photos' => [
            'validation' => 'photos',
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

    // Required by the HasDemographics trait
    public const DEMOGRAPHIC_COLLECTIONS = [
        Demographic::EMPLOYEES_TYPE => [
            'all' => [
                DemographicCollections::ALL,
            ],
        ],
        Demographic::ALL_BENEFICIARIES_TYPE => DemographicCollections::ALL,
        Demographic::INDIRECT_BENEFICIARIES_TYPE => DemographicCollections::INDIRECT,
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(350)
            ->height(211)
            ->nonQueued();
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id', 'uuid');
    }

    public function fundingProgramme(): BelongsTo
    {
        return $this->belongsTo(FundingProgramme::class, 'funding_programme_id', 'uuid');
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'project_pitch_uuid', 'uuid');
    }

    public function treeSpecies()
    {
        return $this->morphMany(TreeSpecies::class, 'speciesable');
    }

    public function toSearchableArray()
    {
        return [
            'project_name' => $this->project_name,
            'organisation_name' => $this->organisation->name ?? null,
        ];
    }

    public static function search($query)
    {
        return self::select('project_pitches.*')
            ->join('organisations', 'project_pitches.organisation_id', '=', 'organisations.uuid')
            ->where('project_pitches.project_name', 'like', "%$query%")
            ->orwhere('organisations.name', 'like', "%$query%");
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function scopeNoSubmissionsForForm(Builder $query, string $uuid): Builder
    {
        if ($uuid) {
            return $query->whereDoesntHave('formSubmissions', function (Builder $query) use ($uuid) {
                $query->where('form_id', $uuid);
            });
        }

        return $query;
    }

    public function scopeHasActiveApplication(Builder $query, bool $filter): Builder
    {
        if ($filter) {
            return $query->whereHas('formSubmissions', function ($qry) {
                $qry->whereNotIn('status', [
                    FormSubmission::STATUS_REJECTED,
                ]);
            });
        } else {
            return $query->whereDoesntHave('formSubmissions', function ($qry) {
                $qry->whereIn('status', [
                    FormSubmission::STATUS_STARTED,
                    FormSubmission::STATUS_AWAITING_APPROVAL,
                    FormSubmission::STATUS_REQUIRES_MORE_INFORMATION,
                    FormSubmission::STATUS_APPROVED,
                ]);
            });
        }
    }
}
