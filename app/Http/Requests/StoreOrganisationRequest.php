<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganisationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => 'required|string|between:1,255',
            'description' => 'required|string|between:1,65535',
            'address_1' => 'required|string|between:1,255',
            'address_2' => 'present|nullable|string|between:1,255',
            'city' => 'required|string|between:1,255',
            'state' => 'present|nullable|string|between:1,255',
            'currency' => 'sometimes|string|between:1,255',
            'zip_code' => 'present|nullable|string|between:1,255',
            'country' => 'required|string',
            'phone_number' => 'required|string|between:1,255',
            'full_time_permanent_employees' => 'numeric|between:0,999999',
            'seasonal_employees' => 'numeric|between:0,999999',
            'part_time_permanent_employees' => 'numeric|between:0,999999',
            'percentage_female' => 'numeric|between:0,100',
            'percentage_youth' => 'numeric|between:0,100',
            'website' => 'present|nullable|string|between:1,255',
            'key_contact' => 'required|string|between:1,255',
            'type' => 'required|string',
            'account_type' => 'string|in:terramatch,ppc,terrafund',
            'category' => 'required|string',
            'facebook' => 'present|nullable|string|starts_with_facebook|between:1,255',
            'twitter' => 'present|nullable|string|starts_with_twitter|between:1,255',
            'linkedin' => 'present|nullable|string|starts_with_linkedin|between:1,255',
            'instagram' => 'present|nullable|string|starts_with_instagram|between:1,255',
            'avatar' => 'present|nullable|integer|exists:uploads,id',
            'cover_photo' => 'present|nullable|integer|exists:uploads,id',
            'video' => 'present|nullable|integer|exists:uploads,id',
            'revenues_19' => 'nullable|numeric|between:0,999999999999.99|regex:/^\d+(\.\d{1,2})?$/',
            'revenues_20' => 'nullable|numeric|between:0,999999999999.99|regex:/^\d+(\.\d{1,2})?$/',
            'revenues_21' => 'nullable|numeric|between:0,999999999999.99|regex:/^\d+(\.\d{1,2})?$/',
            'founded_at' => 'present|nullable|string|date_format:Y-m-d',
            'community_engagement_strategy' => 'nullable|string|between:1,65535',
            'three_year_community_engagement' => 'nullable|string|between:1,65535',
            'women_farmer_engagement' => 'nullable|integer|min:0|max:100',
            'young_people_engagement' => 'nullable|integer|min:0|max:100',
            'monitoring_and_evaluation_experience' => 'nullable|string|between:1,65535',
            'community_follow_up' => 'nullable|string|between:1,65535',
            'total_hectares_restored' => 'nullable|integer|min:0|max:2147483647',
            'hectares_restored_three_years' => 'nullable|integer|min:0|max:2147483647',
            'total_trees_grown' => 'nullable|integer|min:0|max:2147483647',
            'tree_survival_rate' => 'nullable|integer|min:0|max:100',
            'tree_maintenance_and_aftercare' => 'nullable|string|between:1,65535',
        ];
    }
}
