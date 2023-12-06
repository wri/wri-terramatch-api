<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganisationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
            'description' => [
                'sometimes',
                'required',
                'string',
                'between:1,65535',
            ],
            'address_1' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
            'address_2' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'between:1,255',
            ],
            'city' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
            'state' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'between:1,255',
            ],
            'zip_code' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'between:1,255',
            ],
            'country' => [
                'sometimes',
                'required',
                'string',
            ],
            'phone_number' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
            'full_time_permanent_employees' => [
                'sometimes',
                'numeric',
                'between:0,999999',
            ],
            'seasonal_employees' => [
                'sometimes',
                'numeric',
                'between:0,999999',
            ],
            'part_time_permanent_employees' => [
                'sometimes',
                'numeric',
                'between:0,999999',
            ],
            'percentage_female' => [
                'sometimes',
                'numeric',
                'between:0,100',
            ],
            'percentage_youth' => [
                'sometimes',
                'numeric',
                'between:0,100',
            ],
            'website' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'between:1,255',
            ],
            'key_contact' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
            'type' => [
                'sometimes',
                'required',
                'string',
            ],
            'category' => [
                'sometimes',
                'required',
                'string',
            ],
            'facebook' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'starts_with_facebook',
                'between:1,255',
            ],
            'twitter' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'starts_with_twitter',
                'between:1,255',
            ],
            'linkedin' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'starts_with_linkedin',
                'between:1,255',
            ],
            'instagram' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'starts_with_instagram',
                'between:1,255',
            ],
            'avatar' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'exists:uploads,id',
            ],
            'cover_photo' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'exists:uploads,id',
            ],
            'video' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'exists:uploads,id',
            ],
            'founded_at' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'date_format:Y-m-d',
            ],
            'revenues_19' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:0,99999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'revenues_20' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:0,99999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'revenues_21' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:0,99999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'community_engagement_strategy' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'between:1,65535',
            ],
            'three_year_community_engagement' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'between:1,65535',
            ],
            'women_farmer_engagement' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'young_people_engagement' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'monitoring_and_evaluation_experience' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'between:1,65535',
            ],
            'community_follow_up' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'between:1,65535',
            ],
            'total_hectares_restored' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'min:0',
                'max:2147483647',
            ],
            'hectares_restored_three_years' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'min:0',
                'max:2147483647',
            ],
            'total_trees_grown' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'min:0',
                'max:2147483647',
            ],
            'tree_survival_rate' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'min:0',
                'max:100',
            ],
            'tree_maintenance_and_aftercare' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'between:1,65535',
            ],
        ];
    }
}
