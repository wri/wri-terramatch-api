<?php

namespace App\Helpers;

use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Model;

class I18nHelper
{
    public static function generateI18nItem(Model $target, string $property): ?int
    {
        $value = trim(data_get($target, $property, false));

        if (! $value) {
            return data_get($target, $property . '_id');
        }

        $short = strlen($value) <= 256;

        $currentI18nKey = data_get($target, $property . '_id', false);
        if ($currentI18nKey) {
            $i18nItem = I18nItem::find($currentI18nKey);
            if ($i18nItem) {
                $i18nItem->update([
                    'type' => $short ? 'short' : 'long',
                    'status' => I18nItem::STATUS_MODIFIED,
                    'short_value' => $short ? $value : null,
                    'long_value' => $short ? null : $value,
                ]);
                return $i18nItem->id;
            } else {
                return data_get($target, $property . '_id');
            }
        } else {
            $i18nItem = I18nItem::create([
                'type' => $short ? 'short' : 'long',
                'status' => I18nItem::STATUS_DRAFT,
                'short_value' => $short ? $value : null,
                'long_value' => $short ? null : $value,
            ]);

            return $i18nItem->id;
        }

    }
}
