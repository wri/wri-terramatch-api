<?php

namespace App\Models\V2\I18n;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class I18nTranslation extends Model
{
    public $fillable = [
        'i18n_item_id',
        'language',
        'short_value',
        'long_value',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(I18nItem::class);
    }

    public function getValue(): ?string
    {
        return $this->short_value ?? $this->long_value;
    }
}
