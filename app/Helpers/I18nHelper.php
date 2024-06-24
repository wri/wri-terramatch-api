<?php

namespace App\Helpers;

use App\Models\V2\I18n\I18nItem;
use Illuminate\Database\Eloquent\Model;

class I18nHelper
{
    public static function generateI18nItem(Model $target, string $property): ?int
    {
        $shouldGenerateI18nItem = I18nHelper::shouldGenerateI18nItem($target, $property);
        $value = trim(data_get($target, $property, false));
        if ($shouldGenerateI18nItem) {
            $short = strlen($value) < 256;
            $i18nItem = I18nItem::create([
                'type' => $short ? 'short' : 'long',
                'status' => I18nItem::STATUS_DRAFT,
                'short_value' => $short ? $value : null,
                'long_value' => $short ? null : $value,
            ]);

            return $i18nItem->id;
        } else {
            return data_get($target, $property . '_id', null);
        }
    }

    public static function shouldGenerateI18nItem(Model $target, string $property): bool
    {
        $value = trim(data_get($target, $property, false));
        if (! $value) {
            return false;
        }

        $currentI18nKeyId = data_get($target, $property . '_id');
        if (is_null($currentI18nKeyId)) {
            return true;
        }

        $i18nItem = I18nItem::find($currentI18nKeyId);

        if (is_null($i18nItem)) {
            return true;
        }

        $oldType = $i18nItem->type;

        $short = strlen($value) < 256;
        $type = $short ? 'short' : 'long';

        if ($oldType !== $type) {
            return true;
        }

        $oldValue = $short ? $i18nItem->short_value : $i18nItem->long_value;

        return $value !== $oldValue;
    }
}
