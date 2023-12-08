<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class CountryHelper
{
    /**
     * This method iterates over a collection of models and replaces ISO2 codes
     * with the country name.
     */
    public static function codesToNames(Collection $collection, array $fields): Collection
    {
        $countries = array_flip(config('data.countries'));
        foreach ($collection as $item) {
            foreach ($fields as $field) {
                if (! is_null($item->$field)) {
                    $item->$field =
                        array_key_exists($item->$field, $countries) ?
                        $countries[$item->$field] :
                        $item->$field;
                }
            }
        }

        return $collection;
    }
}
