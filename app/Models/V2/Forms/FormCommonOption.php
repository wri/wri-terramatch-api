<?php

namespace App\Models\V2\Forms;

use App\Models\Traits\HasI18nTranslations;
use App\Models\Traits\HasUuid;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FormCommonOption extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasI18nTranslations;

    protected $fillable = [
        'uuid',
        'bucket',
        'slug',
        'alt_value',
        'label',
    ];

    public function toSearchableArray()
    {
        return [
            'label' => $this->label,
        ];
    }

    public static function search($query)
    {
        return self::select('form_common_options.*')
            ->where('form_common_options.label', 'like', "%$query%");
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
            if (empty($model->slug)) {
                $model->slug = self::generateSlug($model->label) ;
            }
        });

        self::saving(function ($model) {
            if (empty($model->getOriginal('slug'))) {
                $model->slug = self::generateSlug($model->label);
            }
        });

        self::updating(function ($model) {
            if (empty($model->getOriginal('slug'))) {
                $model->slug = self::generateSlug($model->label);
            }
        });
    }

    public static function generateSlug(string $value): string
    {
        $i = 0;
        do {
            $suffix = $i === 0 ? '' : '_' . $i;
            $slug = Str::slug($value) . $suffix;
            $i++;
        } while (
            FormCommonOption::where('slug', $slug)->count() > 0
        );

        return $slug;
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
