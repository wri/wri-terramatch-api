<?php

namespace App\Models\V2\Forms;

use App\Models\Framework;
use App\Models\Traits\HasI18nTranslations;
use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\V2\I18n\I18nItem;
use App\Models\V2\MediaModel;
use App\Models\V2\Stages\Stage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Form extends Model implements MediaModel
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasI18nTranslations;
    use HasV2MediaCollections;
    use InteractsWithMedia;
    use HasTypes;

    public const TYPE_APPLICATION = 'application';
    public const TYPE_PROJECT = 'project';
    public const TYPE_PROJECT_REPORT = 'project-report';
    public const TYPE_SITE = 'site';
    public const TYPE_SITE_REPORT = 'site-report';
    public const TYPE_NURSERY = 'nursery';
    public const TYPE_NURSERY_REPORT = 'nursery-report';
    public const TYPE_ORGANISATION = 'organisation';
    public const TYPE_PROJECT_PITCH = 'project-pitch';

    public static $types = [
        self::TYPE_APPLICATION => 'Application',
        self::TYPE_PROJECT => 'Project',
        self::TYPE_PROJECT_REPORT => 'Project Report',
        self::TYPE_SITE => 'Site',
        self::TYPE_SITE_REPORT => 'Site Report',
        self::TYPE_NURSERY => 'Nursery',
        self::TYPE_NURSERY_REPORT => 'Nursery Report',
        self::TYPE_ORGANISATION => 'Organisation',
        self::TYPE_PROJECT_PITCH => 'Project Pitch',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    protected $fillable = [
        'version',
        'title',
        'type',
        'title_id',
        'subtitle',
        'subtitle_id',
        'description',
        'description_id',
        'deadline_at',
        'documentation',
        'documentation_label',
        'submission_message',
        'framework_key',
        'model',
        'duration',
        'published',
        'stage_id',
        'updated_by',
    ];

    public $fileConfiguration = [
        'banner' => [
            'validation' => 'cover-image-with-svg',
            'multiple' => false,
        ],
        'document' => [
            'validation' => 'general-documents',
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

    public function framework(): BelongsTo
    {
        return $this->belongsTo(Framework::class, 'framework_key', 'slug');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(FormSection::class, 'form_id', 'uuid');
    }

    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'form_id', 'uuid');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_id', 'uuid');
    }

    public function toSearchableArray()
    {
        return [
            'title' => $this->title,
        ];
    }

    public static function search($query)
    {
        return self::select('forms.*')
            ->where('forms.title', 'like', "%$query%");
    }

    public function i18nTitle(): BelongsTo
    {
        return $this->belongsTo(I18nItem::class, 'title_id', 'id');
    }

    public function getTranslatedTitleAttribute(): ?string
    {
        return $this->getTranslation('i18nTitle', 'title');
    }

    public function i18nSubtitle(): BelongsTo
    {
        return $this->belongsTo(I18nItem::class, 'subtitle_id', 'id');
    }

    public function getTranslatedSubtitleAttribute(): ?string
    {
        return $this->getTranslation('i18nTitle', 'subtitle');
    }

    public function i18nDescription(): BelongsTo
    {
        return $this->belongsTo(I18nItem::class, 'description_id', 'id');
    }

    public function getTranslatedDescriptionAttribute(): ?string
    {
        return $this->getTranslation('i18nDescription', 'description');
    }
}
