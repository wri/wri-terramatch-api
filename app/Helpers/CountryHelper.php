<?php

namespace App\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class CountryHelper
{
    /**
     * This method iterates over a collection of models and replaces ISO2 codes
     * with the country name.
     */
    public static function codesToNames(Collection $collection, Array $fields): Collection
    {
        $countries = array_flip(Config::get("data.countries"));
        foreach ($collection as $item) {
            foreach ($fields as $field) {
                if (!is_null($item->$field)) {
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
