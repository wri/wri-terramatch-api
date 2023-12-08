<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkTerrafundTreeSpeciesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'treeable_type' => 'required|string|in:programme,nursery,site,site_submission',
            'treeable_id' => 'required|integer',
            'collection' => 'required|array',
        ];
    }
}
