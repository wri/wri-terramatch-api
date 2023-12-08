<?php

namespace App\Http\Requests\V2\Workdays;

use App\Models\V2\Workdays\Workday;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkdayRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => 'sometimes|nullable|integer|between:0,2147483647',
            'collection' => 'sometimes|nullable|string|in:' . implode(',', array_keys(array_merge(Workday::$siteCollections, Workday::$projectCollections))),
            'gender' => 'sometimes|nullable|string|between:1,255',
            'ethnicity' => 'sometimes|nullable|string|between:1,255',
            'age' => 'sometimes|nullable|string|between:1,255',
        ];
    }
}
