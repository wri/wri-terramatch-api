<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkNonTerrafundTreeSpeciesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'speciesable_type' => 'required|string|in:site_submission',
            'speciesable_id' => 'required|integer',
            'collection' => 'required|array',
        ];
    }
}
