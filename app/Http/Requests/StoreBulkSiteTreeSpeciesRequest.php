<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkSiteTreeSpeciesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'site_submission_id' => 'integer|exists:site_submissions,id',
            'tree_species' => 'array',
            'tree_species.*.name' => 'string',
            'tree_species.*.amount' => 'integer|between:0,2147483647',
        ];
    }
}
