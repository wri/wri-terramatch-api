<?php

namespace App\Models\V2;

use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocalizationKey extends Model
{
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
}
