<?php

namespace App\Models\V2\Nurseries;

use App\Models\Framework;
use App\Models\Traits\HasEntityResources;
use App\Models\Traits\HasEntityStatus;
use App\Models\Traits\HasFrameworkKey;
use App\Models\Traits\HasUpdateRequests;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use App\Models\V2\AuditableModel;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\EntityModel;
use App\Models\V2\MediaModel;
use App\Models\V2\Polygon;
use App\Models\V2\Projects\Project;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Nursery extends Model implements MediaModel, AuditableContract, EntityModel, AuditableModel
{
    use HasFrameworkKey;
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use UsesLinkedFields;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use Auditable;
    use HasUpdateRequests;
    use HasEntityStatus;
    use HasEntityResources;

    protected $auditInclude = [
        'status',
        'feedback',
        'feedback_fields',
    ];

    public $table = 'v2_nurseries';

    public $shortName = 'nursery';

    protected $fillable = [
        'framework_key',
        'project_id',
        'status',
        'update_request_status',
        'type',
        'name',
        'start_date',
        'end_date',
        'seedling_grown',
        'planting_contribution',
        'old_model',
        'old_id',
        'feedback',
        'feedback_fields',
        'answers',
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
        'photos' => [
            'validation' => 'photos',
            'multiple' => true,
        ],
    ];

    public $casts = [
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

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'project_name' => data_get($this->project, 'name'),
            'organisation_name' => data_get($this->organisation, 'name'),
        ];
    }

    public static function search($query)
    {
        return self::select('v2_nurseries.*')
            ->join('v2_projects', 'v2_nurseries.project_id', '=', 'v2_projects.id')
            ->join('organisations', 'v2_projects.organisation_id', '=', 'organisations.id')
            ->where('v2_nurseries.name', 'like', "%$query%")
            ->orWhere('v2_projects.name', 'like', "%$query%")
            ->orWhere('organisations.name', 'like', "%$query%");
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
        return empty($this->project) ? $this->project : $this->project->organisation();
    }

    public function reports(): HasMany
    {
        return $this->hasMany(NurseryReport::class);
    }

    public function polygons()
    {
        return $this->morphMany(Polygon::class, 'polygonable');
    }

    public function treeSpecies()
    {
        return $this->morphMany(TreeSpecies::class, 'speciesable')->where('collection', 'nursery-seedling');
    }

    public function getFrameworkUuidAttribute(): ?string
    {
        return $this->framework ? $this->framework->uuid : null;
    }

    public function getNurseryReportsTotalAttribute(): int
    {
        return $this->reports()->count();
    }

    public function getOverdueNurseryReportsTotalAttribute(): int
    {
        return $this->reports()
            ->whereIn(
                'status',
                [ReportStatusStateMachine::STARTED, ReportStatusStateMachine::NEEDS_MORE_INFORMATION]
            )
            ->where('due_at', '<', now())
            ->count();
    }

    public function getSeedlingsGrownCountAttribute(): ?int
    {
        return $this->reports()->sum('seedlings_young_trees') ;
    }

    public function getOrganisationAttribute()
    {
        return $this->project ? $this->project->organisation : null;
    }

    public function scopeCountry(Builder $query, $country): Builder
    {
        return $query->whereHas('project', function ($qry) use ($country) {
            $qry->where('country', $country);
        });
    }

    public function scopeProjectUuid(Builder $query, string $uuid): Builder
    {
        return $query->whereHas('project', function ($qry) use ($uuid) {
            $qry->where('uuid', $uuid);
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

    public function auditStatuses(): MorphMany
    {
        return $this->morphMany(AuditStatus::class, 'auditable');
    }

    public function getAuditableNameAttribute(): string
    {
        return $this->title ?? '';
    }
}
