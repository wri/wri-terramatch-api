<?php

namespace App\Http\Validators;

class OrganisationSubmitValidation
{
    public function rules(): array
    {
        return [
            'type' => 'required|string',
            'private' => 'sometimes|boolean',
            'name' => 'required|string',
            'phone' => 'required|nullable|string',
            'founding_date' => 'sometimes|nullable|date',
            'description' => 'sometimes|nullable|string',

            'countries' => 'sometimes|nullable|array',
            'languages' => 'sometimes|nullable|array',

            'web_url' => 'sometimes|nullable|string',
            'facebook_url' => 'sometimes|nullable|string',
            'instagram_url' => 'sometimes|nullable|string',
            'linkedin_url' => 'sometimes|nullable|string',
            'twitter_url' => 'sometimes|nullable|string',

            'hq_street_1' => 'required|string',
            'hq_street_2' => 'sometimes|nullable|string',
            'hq_city' => 'required|nullable|string',
            'hq_state' => 'required|nullable|string',
            'hq_zipcode' => 'sometimes|nullable|string',
            'hq_country' => 'required|nullable|string',

            'fin_start_month' => 'sometimes|nullable|integer',

            'ha_restored_total' => 'sometimes|nullable|numeric|min:0',
            'ha_restored_3year' => 'sometimes|nullable|numeric|min:0',
            'relevant_experience_years' => 'sometimes|nullable|integer|between:0,150',

            'trees_grown_total' => 'sometimes|nullable|integer|min:0',
            'trees_grown_3year' => 'sometimes|nullable|integer|min:0',
            'tree_care_approach' => 'sometimes|nullable|string',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
