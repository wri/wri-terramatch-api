<?php

namespace App\Http\Requests\V2;

use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFundingProgrammeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => ['string', 'min:1', 'max:255'],
            'description' => ['string', 'min:1', 'max:65000'],
            'status' => ['string', 'in:' . implode(',', array_keys(FundingProgramme::$statuses))],
            'location' => ['nullable', 'string', 'min:1', 'max:65000'],
            'read_more_url' => ['nullable', 'string', 'url', 'min:1', 'max:65000'],
            'organisation_types' => ['array'],
            'organisation_types.*' => ['string', 'in:' . implode(',', array_keys(Organisation::$types))],
            'framework_key' => ['sometimes', 'string', 'min:1', 'max:250'],
        ];
    }
}
