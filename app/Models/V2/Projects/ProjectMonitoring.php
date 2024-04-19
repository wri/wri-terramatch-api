<?php

namespace App\Models\V2\Projects;

use App\Models\Framework;
use App\Models\Traits\HasFrameworkKey;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\V2\MediaModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProjectMonitoring extends Model implements MediaModel
{
    use HasFactory;
    use HasUuid;
    use HasStatus;
    use HasFrameworkKey;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use SoftDeletes;

    public const STATUS_TEMP = 'temp';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    public static $statuses = [
        self::STATUS_TEMP => 'Temporary',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_ARCHIVED => 'Archived',
    ];

    public $table = 'v2_project_monitorings';

    protected $fillable = [
        'framework_key',
        'project_id',
        'status',
        'total_hectares',
        'ha_mangrove',
        'ha_assisted',
        'ha_agroforestry',
        'ha_reforestation',
        'ha_peatland',
        'ha_riparian',
        'ha_enrichment',
        'ha_nucleation',
        'ha_silvopasture',
        'ha_direct',
        'tree_count',
        'tree_cover',
        'tree_cover_loss',
        'carbon_benefits',
        'number_of_esrp',
        'field_tree_count',
        'field_tree_regenerated',
        'field_tree_survival_percent',
        'start_date',
        'end_date',
        'last_updated',
        'old_id',
        'old_model',
    ];

    public $casts = [
        'total_hectares' => 'float',
        'ha_mangrove' => 'float',
        'ha_assisted' => 'float',
        'ha_agroforestry' => 'float',
        'ha_reforestation' => 'float',
        'ha_peatland' => 'float',
        'ha_riparian' => 'float',
        'ha_enrichment' => 'float',
        'ha_nucleation' => 'float',
        'ha_silvopasture' => 'float',
        'ha_direct' => 'float',
        'tree_count' => 'float',
        'tree_cover' => 'float',
        'tree_cover_loss' => 'float',
        'carbon_benefits' => 'float',
        'number_of_ersp' => 'float',
        'field_tree_count' => 'float',
        'field_tree_regenerated' => 'float',
        'field_tree_survival_percent' => 'float',
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function framework(): BelongsTo
    {
        return $this->belongsTo(Framework::class,  'framework_key', 'slug');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
