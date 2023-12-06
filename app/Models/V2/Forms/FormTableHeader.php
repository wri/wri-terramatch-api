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

class FormTableHeader extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasI18nTranslations;

    protected $fillable = [
        'uuid',
        'form_question_id',
        'order',
        'slug',
        'label',
        'label_id',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
            $model->slug = self::generateSlug($model->label, $model->form_question_id) ;
        });

        self::saving(function ($model) {
            if (empty($model->getOriginal('slug'))) {
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
            FormTableHeader::where('form_question_id', $formQuestionId)->where('slug', $slug)->count() > 0
        );

        return $slug;
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
