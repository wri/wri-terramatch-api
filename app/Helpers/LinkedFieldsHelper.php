<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class LinkedFieldsHelper
{
    public static Collection $fields;

    public static function config(): array
    {
        return config('wri.linked-fields', []);
    }

    public static function fields(): Collection
    {
        $includes = ['fields', 'file-collections', 'relations'];

        if ((new \ReflectionProperty(self::class, 'fields'))->isInitialized()) {
            return self::$fields;
        }

        self::$fields = collect();

        foreach (data_get(self::config(), 'models', []) as $model) {
            foreach ($includes as $section) {
                foreach (data_get($model, $section, []) as $fieldKey => $value) {
                    throw_if(
                        self::fields()->where('field_key', $fieldKey)->count() > 0,
                        "Repeated field [$fieldKey]"
                    );

                    self::$fields->add(array_merge(['field_key' => $fieldKey], $value));
                }
            }
        }

        return self::$fields;
    }

    public static function getPropertyNameByFieldKey(string $fieldKey): string
    {
        $candidate = self::fields()->where('field_key', $fieldKey)->first();

        if (is_null($candidate)) {
            return $fieldKey;
        }

        return $candidate['property'];
    }
}
