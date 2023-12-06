<?php

namespace App\Validators;

class TargetValidator extends Validator
{
    public const CREATE = [
        'monitoring_id' => 'required|integer|exists:monitorings,id',
        'start_date' => 'required|string|date_format:Y-m-d',
        'finish_date' => 'required|string|date_format:Y-m-d|after:start_date',
        'funding_amount' => 'required|integer|between:1,2147483647',
        'land_geojson' => 'present|nullable|string|json|geo_json|between:1,4294967295',
        'data' => 'present|array|array_object',
    ];

    public const CREATE_DATA = [
        'trees_planted' => 'integer|between:1,2147483647',
        'non_trees_planted' => 'integer|between:1,2147483647',
        'survival_rate' => 'integer|between:0,100',
        'land_size_planted' => 'numeric|strict_float|between:0,999999',
        'land_size_restored' => 'numeric|strict_float|between:0,999999',
        'carbon_captured' => 'integer|between:1,2147483647',
        'supported_nurseries' => 'integer|between:1,2147483647',
        'nurseries_production_amount' => 'integer|between:1,2147483647',
        'short_term_jobs_amount' => 'integer|between:1,2147483647',
        'long_term_jobs_amount' => 'integer|between:1,2147483647',
        'volunteers_amount' => 'integer|between:1,2147483647',
        'training_amount' => 'integer|between:1,2147483647',
        'benefited_people' => 'integer|between:1,2147483647',
    ];
}
