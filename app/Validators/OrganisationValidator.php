<?php

namespace App\Validators;

class OrganisationValidator extends Validator
{
    public $create = [
        "name" => "required|string|between:1,255",
        "description" => "required|string|min:8",
        "address_1" => "required|string|between:1,255",
        "address_2" => "present|nullable|string|between:1,255",
        "city" => "required|string|between:1,255",
        "state" => "present|nullable|string|between:1,255",
        "zip_code" => "present|nullable|string|between:1,255",
        "country" => "required|string|country_code",
        "phone_number" => "required|string|between:1,255",
        "website" => "present|nullable|string|soft_url|between:1,255",
        "type" => "required|string|organisation_type",
        "category" => "required|string|organisation_category",
        "facebook" => "present|nullable|string|soft_url|starts_with_facebook|between:1,255",
        "twitter" => "present|nullable|string|soft_url|starts_with_twitter|between:1,255",
        "linkedin" => "present|nullable|string|soft_url|starts_with_linkedin|between:1,255",
        "instagram" => "present|nullable|string|soft_url|starts_with_instagram|between:1,255",
        "avatar" => "present|nullable|integer|exists:uploads,id",
        "cover_photo" => "present|nullable|integer|exists:uploads,id",
        "video" => "present|nullable|integer|exists:uploads,id",
        "founded_at" => "present|nullable|string|date_format:Y-m-d"
    ];

    public $update = [
        "name" => "sometimes|required|string|between:1,255",
        "description" => "sometimes|required|string|min:8",
        "address_1" => "sometimes|required|string|between:1,255",
        "address_2" => "sometimes|present|nullable|string|between:1,255",
        "city" => "sometimes|required|string|between:1,255",
        "state" => "sometimes|present|nullable|string|between:1,255",
        "zip_code" => "sometimes|present|nullable|string|between:1,255",
        "country" => "sometimes|required|string|country_code",
        "phone_number" => "sometimes|required|string|between:1,255",
        "website" => "sometimes|present|nullable|string|soft_url|between:1,255",
        "type" => "sometimes|required|string|organisation_type",
        "category" => "sometimes|required|string|organisation_category",
        "facebook" => "sometimes|present|nullable|string|soft_url|starts_with_facebook|between:1,255",
        "twitter" => "sometimes|present|nullable|string|soft_url|starts_with_twitter|between:1,255",
        "linkedin" => "sometimes|present|nullable|string|soft_url|starts_with_linkedin|between:1,255",
        "instagram" => "sometimes|present|nullable|string|soft_url|starts_with_instagram|between:1,255",
        "avatar" => "sometimes|present|nullable|integer|exists:uploads,id",
        "cover_photo" => "sometimes|present|nullable|integer|exists:uploads,id",
        "video" => "sometimes|present|nullable|integer|exists:uploads,id",
        "founded_at" => "sometimes|present|nullable|string|date_format:Y-m-d"
    ];
}