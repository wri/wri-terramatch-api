<?php

namespace App\Models\V2\Forms;

use App\Models\Traits\HasI18nTranslations;
use App\Models\Traits\HasLinkedFields;
use App\Models\Traits\HasUuid;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormQuestion extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasI18nTranslations;
    use HasLinkedFields;

    protected $fillable = [
        'form_section_id',
        'parent_id',
        'input_type',
        'name',
        'validation',
        'multichoice',
        'collection',
        'order',
        'linked_field_key',
        'label',
        'label_id',
        'placeholder',
        'placeholder_id',
        'description',
        'description_id',
        'additional_props',
        'additional_text',
        'additional_url',
        'options_list',
        'options_other',
        'show_on_parent_condition',
    ];

    protected $with = [
        'options', 'i18nLabel', 'i18nPlaceholder', 'tableHeaders', 'i18nDescription',
    ];

    protected $casts = [
        'validation' => 'json',
        'multichoice' => 'boolean',
        'additional_props' => 'json',
        'options_other' => 'boolean',
        'show_on_parent_condition' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(FormSection::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(FormQuestion::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(FormQuestion::class, 'parent_id', 'uuid');
    }

    public function options(): HasMany
    {
        return $this->hasMany(FormQuestionOption::class, 'form_question_id');
    }

    public function commonOptions(): BelongsToMany
    {
        return $this->belongsToMany(FormCommonOption::class, 'form_common_options_questions', 'id');
    }

    public function tableHeaders(): HasMany
    {
        return $this->hasMany(FormTableHeader::class, 'form_question_id', 'id');
    }

    public function i18nLabel(): BelongsTo
    {
        return $this->belongsTo(I18nItem::class, 'label_id', 'id');
    }

    public function getTranslatedLabelAttribute(): ?string
    {
        return $this->getTranslation('i18nLabel', 'label');
    }

    public function i18nPlaceholder(): BelongsTo
    {
        return $this->belongsTo(I18nItem::class, 'placeholder_id', 'id');
    }

    public function getTranslatedPlaceholderAttribute(): ?string
    {
        return $this->getTranslation('i18nPlaceholder', 'placeholder');
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
