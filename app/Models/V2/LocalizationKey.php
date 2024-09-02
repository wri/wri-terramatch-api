<?php

namespace App\Models\V2;

use App\Models\Traits\HasI18nTranslations;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalizationKey extends Model
{
    use HasI18nTranslations;

    protected $fillable = [
        'key',
        'value',    
        'value_id',
        'tag'
    ];

    public $timestamps = false;

    public function i18nValue(): BelongsTo
    {
        return $this->belongsTo(I18nItem::class, 'value_id', 'id');
    }

    public function getTranslatedValueAttribute(): ?string
    {
        return $this->getTranslation('i18nValue', 'value');
    }
}
