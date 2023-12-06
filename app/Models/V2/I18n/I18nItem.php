<?php

namespace App\Models\V2\I18n;

use App\Models\Traits\HasStatus;
use App\Models\Traits\HasTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class I18nItem extends Model
{
    use HasStatus;
    use HasTypes;

    /*  Statuses    */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_TRANSLATED = 'translated';
    public const STATUS_MODIFIED = 'modified';

    public static $statuses = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_PENDING => 'Pending Approval',
        self::STATUS_TRANSLATED => 'Translated',
        self::STATUS_MODIFIED => 'Modified',
    ];

    public const TYPE_SHORT = 'short';
    public const TYPE_LONG = 'long';

    public static $types = [
        self::TYPE_SHORT => 'Short',
        self::TYPE_LONG => 'Long',
    ];

    public $fillable = [
        'type',
        'status',
        'short_value',
        'long_value',
        'hash',
    ];

    public static function boot()
    {
        parent::boot();

        self::saving(function ($model) {
            $model->hash = md5($model->value);
            if (empty($model->getOriginal('hash'))) {
                $model->status = I18nItem::STATUS_DRAFT;
            } elseif ($model->hash !== $model->getOriginal('hash')) {
                $model->status = I18nItem::STATUS_MODIFIED;
            }
        });

        self::updating(function ($model) {
            $model->hash = md5($model->value);
            if ($model->hash !== $model->getOriginal('hash')) {
                $model->status = I18nItem::STATUS_MODIFIED;
            }
        });
    }

    public function translations(): HasMany
    {
        return $this->hasMany(I18nTranslation::class);
    }

    public function getValueAttribute(): ?string
    {
        return data_get($this, 'short_value', $this->long_value);
    }

    public function getTranslated(string $language): ?I18nTranslation
    {
        return $this->translations()->where('language', $language)->first();
    }
}
