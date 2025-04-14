<?php

namespace App\Models\V2;

use App\Models\Traits\HasI18nTranslations;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\V2\Forms\Application;
use App\Models\V2\I18n\I18nItem;
use App\Models\V2\Stages\Stage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FundingProgramme extends Model implements MediaModel
{
    use HasFactory;
    use HasStatus;
    use HasUuid;
    use SoftDeletes;
    use HasV2MediaCollections;
    use InteractsWithMedia;
    use HasI18nTranslations;

    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_DISABLED = 'disabled';
    public const STATUS_COMING_SOON = 'coming-soon';

    public static $statuses = [
        self::STATUS_INACTIVE => 'Inactive',
        self::STATUS_ACTIVE => 'Active',
        self::STATUS_DISABLED => 'Disabled',
        self::STATUS_COMING_SOON => 'Coming Soon',
    ];

    protected $with = ['stages', 'organisations'];

    protected $fillable = [
        'name',
        'name_id',
        'status',
        'location',
        'location_id',
        'read_more_url',
        'description',
        'description_id',
        'organisation_types',
        # Note: after investigation in April 2025, it looks like this is being used to fetch actual frameworks by
        # access code instead of by framework key. See TODO in ApplicationStatus.tsx.
        'framework_key',
    ];

    protected $casts = [
        'organisation_types' => 'array',
    ];

    public $fileConfiguration = [
        'cover' => [
            'validation' => 'cover-image',
            'multiple' => false,
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

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class, 'funding_programme_id', 'uuid');
    }

    public function i18nName(): BelongsTo
    {
        return $this->belongsTo(I18nItem::class, 'name_id', 'id');
    }

    public function getTranslatedNameAttribute(): ?string
    {
        return $this->getTranslation('i18nName', 'name');
    }

    public function i18nDescription(): BelongsTo
    {
        return $this->belongsTo(I18nItem::class, 'description_id', 'id');
    }

    public function i18nLocation(): BelongsTo
    {
        return $this->belongsTo(I18nItem::class, 'location_id', 'id');
    }

    public function getTranslatedLocationAttribute(): ?string
    {
        return $this->getTranslation('i18nLocation', 'location');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'funding_programme_uuid', 'uuid');
    }

    public function organisations(): HasManyThrough
    {
        return $this->hasManyThrough(
            Organisation::class,
            Application::class,
            'funding_programme_uuid',
            'uuid',
            'uuid',
            'organisation_uuid'
        );
    }

    public function getTranslatedDescriptionAttribute(): ?string
    {
        return $this->getTranslation('i18nDescription', 'description');
    }
}
