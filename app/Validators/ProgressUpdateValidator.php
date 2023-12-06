<?php

namespace App\Validators;

class ProgressUpdateValidator extends Validator
{
    public const CREATE = [
        'monitoring_id' => 'required|integer|exists:monitorings,id',
        'grouping' => 'required|string|in:general,planting,monitoring',
        'title' => 'required|string|between:1,255',
        'breakdown' => 'required|string|between:1,65535',
        'summary' => 'required|string|between:1,65535',
        'data' => 'present|array|array_object',
        'images' => 'present|array|array_array|max:20',
        'images.*' => 'array|array_object',
    ];
    public const CREATE_DATA = [
        'planting_date' => 'string|date_format:Y-m-d',
        'trees_planted' => 'array|array_array|min:1',
        'trees_planted.*' => 'array|array_object',
        'trees_planted.*.name' => 'string|between:1,255',
        'trees_planted.*.value' => 'integer|between:0,2147483647',
        'non_trees_planted' => 'array|array_array|min:1',
        'non_trees_planted.*' => 'array|array_object',
        'non_trees_planted.*.name' => 'string|between:1,255',
        'non_trees_planted.*.value' => 'integer|between:0,2147483647',
        'survival_amount' => 'array|array_array|min:1',
        'survival_amount.*' => 'array|array_object',
        'survival_amount.*.name' => 'string|between:1,255',
        'survival_amount.*.value' => 'integer|between:0,2147483647',
        'supported_nurseries' => 'integer|between:0,2147483647',
        'survival_rate' => 'integer|between:0,100',
        'carbon_captured' => 'integer|between:0,2147483647',
        'nurseries_production_amount' => 'integer|between:0,2147483647',
        'land_size_planted' => 'numeric|strict_float|between:0,999999',
        'land_size_restored' => 'numeric|strict_float|between:0,999999',
        'short_term_jobs_amount' => 'array|array_object',
        'short_term_jobs_amount.male' => 'integer|between:0,2147483647',
        'short_term_jobs_amount.female' => 'integer|between:0,2147483647',
        'long_term_jobs_amount' => 'array|array_object',
        'long_term_jobs_amount.male' => 'integer|between:0,2147483647',
        'long_term_jobs_amount.female' => 'integer|between:0,2147483647',
        'volunteers_amount' => 'array|array_object',
        'volunteers_amount.male' => 'integer|between:0,2147483647',
        'volunteers_amount.female' => 'integer|between:0,2147483647',
        'training_amount' => 'array|array_object',
        'training_amount.male' => 'integer|between:0,2147483647',
        'training_amount.female' => 'integer|between:0,2147483647',
        'benefited_people' => 'array|array_object',
        'benefited_people.male' => 'integer|between:0,2147483647',
        'benefited_people.female' => 'integer|between:0,2147483647',
        'mortality_causes' => 'string|between:1,65535',
        'challenges_update' => 'string|between:1,65535',
        'insights_update' => 'string|between:1,65535',
        'biodiversity_update' => 'string|between:1,65535',
    ];

    public const CREATE_IMAGE = [
        'image' => 'required|integer|exists:uploads,id',
        'caption' => 'required|string|between:1,255',
    ];
}
