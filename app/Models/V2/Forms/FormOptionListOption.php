<?php

namespace App\Models\V2\Forms;

use App\Models\Traits\HasI18nTranslations;
use App\Models\Traits\HasUuid;
use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class FormOptionListOption extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use Searchable;
    use HasI18nTranslations;

    protected $guarded = [];

    public function toSearchableArray()
    {
        return [
            'label' => $this->label,
        ];
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
