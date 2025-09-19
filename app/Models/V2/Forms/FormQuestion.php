<?php

namespace App\Models\V2\Forms;

use App\Models\Traits\HasI18nTranslations;
use App\Models\Traits\HasUuid;
use App\Models\V2\I18n\I18nItem;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class FormQuestion extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasI18nTranslations;

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
        'is_parent_conditional_default',
        'min_character_limit',
        'max_character_limit',
        'years',
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
        'is_parent_conditional_default' => 'boolean',
        'min_character_limit' => 'integer',
        'max_character_limit' => 'integer',
        'years' => 'array',
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

    public function getLinkedFieldInfo(array $params = null): ?array
    {
        $info = $this->getLinkedFieldConfig();

        if (is_array($info)) {
            $info['uuid'] = $this->getLinkedModelUuid($info, $params);
        }

        return $info;
    }

    protected function getLinkedFieldConfig(): ?array
    {
        $types = ['fields', 'file-collections', 'relations'];
        foreach (config('wri.linked-fields.models', []) as $modelKey => $cfgModel) {
            foreach ($types as $type) {
                if (Arr::has($cfgModel, $type. '.' . $this->linked_field_key)) {
                    $questionProps = data_get($cfgModel[$type], $this->linked_field_key);
                    $modelProps = [
                        'model-label' => data_get($cfgModel, 'label'),
                        'model-key' => $modelKey,
                        'model' => data_get($cfgModel, 'model'),
                        'link-type' => $type,
                    ];

                    return array_merge($modelProps, $questionProps);
                }
            }
        }

        return null;
    }

    protected function getLinkedModelUuid($cfg, array $params = null): ?string
    {
        if (! empty(data_get($params, 'model_uuid'))) {
            return data_get($params, 'model_uuid');
        }

        return $this->getApplicationModelsUuids($cfg, $params);
    }

    protected function getApplicationModelsUuids($cfg, array $params = null): ?string
    {
        $organisation = data_get($params, 'organisation', null);
        if (empty($organisation) && ! empty(data_get($params, 'organisation_uuid', null))) {
            $organisation = Organisation::isUuid(data_get($params, 'organisation_uuid'))->first();
        }

        $projectPitch = data_get($params, 'project-pitch', null);

        if (empty($projectPitch) && ! empty(data_get($params, 'project_pitch_uuid', null))) {
            $projectPitch = ProjectPitch::isUuid(data_get($params, 'project_pitch_uuid'))->first();
        }

        if ($organisation && empty($projectPitch)) {
            $projectPitch = $organisation->projectPitches()->first();
        }

        switch ($cfg['model-key']) {
            case 'organisation':
                return empty($organisation) ? null : $organisation->uuid;
            case 'project-pitch':
                return empty($projectPitch) ? null : $projectPitch->uuid;
            default:
                return null;
        }
    }
}
