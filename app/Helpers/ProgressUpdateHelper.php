<?php

namespace App\Helpers;

use App\Models\ProgressUpdate as ProgressUpdateModel;

class ProgressUpdateHelper
{
    private function __construct()
    {
    }

    public const TOTAL_RULES = [
        'trees_planted' => 'ARRAY',
        'non_trees_planted' => 'ARRAY',
        'survival_amount' => 'ARRAY',
        'short_term_jobs_amount' => 'OBJECT',
        'long_term_jobs_amount' => 'OBJECT',
        'volunteers_amount' => 'OBJECT',
        'training_amount' => 'OBJECT',
        'benefited_people' => 'OBJECT',
    ];

    public static function total(array $data): array
    {
        foreach ($data as $attribute => $datum) {
            if (! array_key_exists($attribute, ProgressUpdateHelper::TOTAL_RULES)) {
                continue;
            }
            $rule = ProgressUpdateHelper::TOTAL_RULES[$attribute];
            switch ($rule) {
                case 'ARRAY':
                    $total = 0;
                    foreach ($datum as $value) {
                        $total += $value['value'];
                    }

                    break;
                case 'OBJECT':
                    $total = array_sum($datum);

                    break;
            }
            $newAttribute = $attribute . '_total';
            $data[$newAttribute] = $total;
        }

        return $data;
    }

    public const SUMMARISE_RULES = [
        'trees_planted' => 'ARRAY_TOTAL',
        'non_trees_planted' => 'ARRAY_TOTAL',
        'survival_amount' => 'ARRAY_TOTAL',
        'supported_nurseries' => 'INTEGER_TOTAL',
        'survival_rate' => 'INTEGER_LATEST',
        'carbon_captured' => 'INTEGER_TOTAL',
        'nurseries_production_amount' => 'INTEGER_TOTAL',
        'land_size_planted' => 'FLOAT_TOTAL',
        'land_size_restored' => 'FLOAT_TOTAL',
        'short_term_jobs_amount' => 'OBJECT_TOTAL',
        'long_term_jobs_amount' => 'OBJECT_TOTAL',
        'volunteers_amount' => 'OBJECT_TOTAL',
        'training_amount' => 'OBJECT_TOTAL',
        'benefited_people' => 'OBJECT_TOTAL',
    ];

    public static function summarise(Object $datum, ProgressUpdateModel $progressUpdate)
    {
        $attribute = $datum->attribute;
        if (
            ! array_key_exists($attribute, $progressUpdate->data) ||
            ! array_key_exists($attribute, ProgressUpdateHelper::SUMMARISE_RULES)
        ) {
            return $datum;
        }
        $rule = ProgressUpdateHelper::SUMMARISE_RULES[$attribute];
        switch ($rule) {
            case 'ARRAY_TOTAL':
            case 'OBJECT_TOTAL':
                if (is_null($datum->progress_update)) {
                    $datum->progress_update = 0;
                }
                $totalAttribute = $attribute . '_total';
                $datum->progress_update += $progressUpdate->data[$totalAttribute];

                break;
            case 'INTEGER_LATEST':
                $datum->progress_update = $progressUpdate->data[$attribute];

                break;
            case 'INTEGER_TOTAL':
            case 'FLOAT_TOTAL':
                if (is_null($datum->progress_update)) {
                    $datum->progress_update = 0;
                }
                $datum->progress_update += $progressUpdate->data[$attribute];

                break;
        }
        $datum->updated_at = $progressUpdate->created_at;

        return $datum;
    }
}
