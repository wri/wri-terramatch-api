<?php

namespace App\Helpers;

use Exception;

class ErrorHelper
{
    private function __construct()
    {
    }

    public static function create(String $attribute, String $pretty, String $code, String $message): array
    {
        return [
            $attribute => [
                json_encode([
                    $code,
                    'The {{attribute}} ' . $message . '.',
                    (object) [
                        'attribute' => $pretty,
                    ],
                    'The ' . $pretty . ' ' . $message . '.',
                ]),
            ],
        ];
    }

    public static function prefix(array $errors, String $value): array
    {
        foreach ($errors as $source => &$messages) {
            foreach ($messages as &$message) {
                $parts = json_decode($message);
                if (json_last_error() > 0 || ! is_array($parts) || count($parts) != 4) {
                    throw new Exception();
                }
                $parts[2]->attribute = $value . str_replace(' ', '_', $parts[2]->attribute);
                $search = array_map(
                    function ($a) {
                        return '{{' . $a . '}}';
                    },
                    array_keys(get_object_vars($parts[2]))
                );
                $replace = array_values(get_object_vars($parts[2]));
                $parts[3] = str_replace($search, $replace, $parts[1]);
                $message = json_encode($parts);
            }
        }

        return $errors;
    }
}
