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

    public static function sortBy(Array $elements, String $property, Bool $direction): Array
    {
        $elements = Arr::sort($elements, function($element) use ($property) {
            return $element->$property;
        });
        if ($direction == ArrayHelper::DESC) {
            $elements = array_reverse($elements);
        }
        return array_values($elements);
    }

    public static function sortDataBy(Array $elements, String $property, Bool $direction): Array
    {
        $elements = Arr::sort($elements, function($element) use ($property) {
            return $element->data->$property;
        });
        if ($direction == ArrayHelper::DESC) {
            $elements = array_reverse($elements);
        }
        return array_values($elements);
    }
}