<?php

namespace App\Validators;

class SiteDisturbanceValidator extends Validator
{
    public const CREATE = [
        'site_submission_id' => 'required|integer|exists:site_submissions,id',
        'disturbance_type' => 'required|string|in:ecological,climatic,manmade',
        'extent' => 'required|string|in:0-20,21-40,41-60,61-80,81-100',
        'intensity' => 'required|string|in:low,medium,high',
        'description' => 'nullable|string|max:4294967295',
    ];

    public const UPDATE = [
        'disturbance_type' => 'string|in:ecological,climatic,manmade',
        'extent' => 'string|in:0-20,21-40,41-60,61-80,81-100',
        'intensity' => 'string|in:low,medium,high',
        'description' => 'string|max:4294967295',
    ];

    public const CREATE_DISTURBANCE_INFORMATION = [
        'site_submission_id' => 'required|integer|exists:site_submissions,id',
        'disturbance_information' => 'required|string|max:4294967295',
    ];

    public const UPDATE_DISTURBANCE_INFORMATION = [
        'disturbance_information' => 'required|string|max:4294967295',
    ];
}
