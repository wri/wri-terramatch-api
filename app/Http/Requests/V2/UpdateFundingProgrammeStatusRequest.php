<?php

namespace App\Http\Requests\V2;

use App\Models\V2\FundingProgramme;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFundingProgrammeStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'status' => ['string', 'in:' . implode(',', array_keys(FundingProgramme::$statuses))],
        ];
    }
}
