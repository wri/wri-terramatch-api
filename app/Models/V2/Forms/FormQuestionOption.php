<?php

namespace App\Models\V2\Forms;

use App\Models\Traits\HasI18nTranslations;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FormQuestionOption extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasI18nTranslations;
    use HasV2MediaCollections;
    use InteractsWithMedia;

    protected $fillable = [
        'form_question_id',
        'order',
        'slug',
        'label',
        'label_id',
        'image_url',
    ];

    public $fileConfiguration = [
        'image' => [
            'validation' => 'photos',
            'multiple' => false,
        ],
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
            if (empty($model->slug)) {
                $model->slug = self::generateSlug($model->label, $model->form_question_id);
            }
        });

        self::saving(function ($model) {
            if (empty($model->slug) && empty($model->getOriginal('slug'))) {
                $model->slug = self::generateSlug($model->label, $model->form_question_id);
            }
        });

        self::updating(function ($model) {
            if (empty($model->getOriginal('slug'))) {
                $model->slug = self::generateSlug($model->label, $model->form_question_id);
            }
        });
    }

    public static function generateSlug(string $value, $formQuestionId): string
    {
        $i = 0;
        do {
            $suffix = $i === 0 ? '' : '_' . $i;
            $slug = Str::slug($value) . $suffix;
            $i++;
        } while (
            FormQuestionOption::where('form_question_id', $formQuestionId)->where('slug', $slug)->count() > 0
        );

        return $slug;
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(350)
            ->height(211)
            ->nonQueued();
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(FormQuestion::class, 'form_question_id');
    }

    public function i18nLabel(): BelongsTo
    {
        return $this->belongsTo(I18nItem::class, 'label_id', 'id');
    }

    public function getTranslatedLabelAttribute(): ?string
    {
        return $this->getTranslation('i18nLabel', 'label');
    }
}
