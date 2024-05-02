<?php

namespace App\Models\V2\Sites;

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

class SiteMonitoring extends Model implements MediaModel
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

    public $table = 'v2_site_monitorings';

    protected $fillable = [
        'framework_key',
        'status',
        'tree_count',
        'tree_cover',
        'field_tree_count',
        'field_tree_count',
        'measurement_date',
        'last_updated',
        'old_id',
        'old_model',
        'site_id',
    ];

    public $casts = [
        'tree_count' => 'float',
        'tree_cover' => 'float',
        'field_tree_count' => 'float',
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

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
