<?php

namespace App\Helpers;

use Illuminate\Support\Arr;

class ArrayHelper
{
    public const ASC = true;
    public const DESC = false;

    private function __construct()
    {
    }

    public static function sortBy(array $elements, String $property, Bool $direction): array
    {
        $elements = Arr::sort($elements, function ($element) use ($property) {
            return $element->$property;
        });
        if ($direction == ArrayHelper::DESC) {
            $elements = array_reverse($elements);
        }

        return array_values($elements);
    }

    public static function sortDataBy(array $elements, String $property, Bool $direction): array
    {
        $elements = Arr::sort($elements, function ($element) use ($property) {
            return $element->data->$property;
        });
        if ($direction == ArrayHelper::DESC) {
            $elements = array_reverse($elements);
        }

        return array_values($elements);
    }
}
