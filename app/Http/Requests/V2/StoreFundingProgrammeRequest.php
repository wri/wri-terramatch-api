<?php

namespace App\Http\Requests\V2;

use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Http\FormRequest;

class StoreFundingProgrammeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['required', 'string', 'min:1', 'max:65000'],
            'location' => ['sometimes', 'nullable', 'string', 'min:1', 'max:65000'],
            'read_more_url' => ['sometimes', 'nullable', 'string', 'url', 'min:1', 'max:65000'],
            'description' => ['required', 'string', 'min:1', 'max:65000'],
            'status' => ['required', 'string', 'in:' . implode(',', array_keys(FundingProgramme::$statuses))],
            'organisation_types' => ['required', 'array'],
            'organisation_types.*' => ['required', 'string', 'in:' . implode(',', array_keys(Organisation::$types))],
        ];
    }
}
