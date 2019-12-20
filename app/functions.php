<?php

function is_collection($collection): bool
{
    if (!is_object($collection)) {
        return false;
    } else if (get_class($collection) != "Illuminate\\Database\\Eloquent\\Collection") {
        return false;
    } else {
        return true;
    }
}

function repair_url(?string $url): ?string
{
    if (is_null($url)) {
        return $url;
    }
    $startsWithHttp = strtolower(substr($url, 0, 7)) == "http://";
    $startsWithHttps = strtolower(substr($url, 0, 8)) == "https://";
    return ($startsWithHttp || $startsWithHttps) ? $url : ("http://" . $url);
}

function explode_pop(string $delimiter, string $string): string
{
    $parts = explode($delimiter, $string);
    return array_pop($parts);
}

function total_properties(object $object, array $properties): float
{
    $total = 0;
    foreach ($properties as $property) {
        $total += $object->$property;
    }
    return $total;
}