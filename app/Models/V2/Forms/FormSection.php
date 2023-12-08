<?php

namespace App\Models\V2\Forms;

use App\Models\Traits\HasI18nTranslations;
use App\Models\Traits\HasUuid;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormSection extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasI18nTranslations;

    protected $with = ['questions'];

    protected $casts = [
        'required' => 'boolean',
        'multichoice' => 'boolean',
    ];

    protected $fillable = [
        'form_id',
        'order',
        'title',
        'title_id',
        'subtitle',
        'subtitle_id',
        'description',
        'description_id',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id', 'uuid');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(FormQuestion::class);
    }

    public function nonDependantQuestions(): HasMany
    {
        return $this->hasMany(FormQuestion::class)->whereNull('parent_id');
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
        return $this->getTranslation('i18nSubtitle', 'subtitle');
    }

    public function i18nDescription(): BelongsTo
    {
        return $this->belongsTo(I18nItem::class, 'description_id', 'id');
    }

    public function getTranslatedDescriptionAttribute(): ?string
    {
        return $this->getTranslation('i18nDescription', 'description');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
