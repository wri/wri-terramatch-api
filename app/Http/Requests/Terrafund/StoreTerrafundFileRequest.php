<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrafundFileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'fileable_type' => 'required|string|in:programme,nursery,site,nursery_submission,programme_submission,site_submission',
            'fileable_id' => 'required|integer',
            'upload' => 'required|integer|exists:uploads,id',
            'is_public' => 'required|boolean',
            'location_long' => 'nullable|numeric|between:-180,180',
            'location_lat' => 'nullable|numeric|between:-90,90',
            'collection' => 'nullable|string|max:255',
        ];
    }
}
