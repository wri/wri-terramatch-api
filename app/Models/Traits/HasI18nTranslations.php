<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Lang;

trait HasI18nTranslations
{
    public function getTranslation($i18nProp, $property): ?string
    {
        $i18nItem = $this->$i18nProp;
        if (empty($i18nItem)) {
            return $this->$property;
        }

        $trans = $i18nItem->translations->where('language', Lang::locale())->first();

        return ! empty($trans) ? $trans->getValue() : $this->$property;
    }
}
