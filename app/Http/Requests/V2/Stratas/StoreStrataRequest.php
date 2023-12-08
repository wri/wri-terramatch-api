<?php

namespace App\Http\Requests\V2\Stratas;

use Illuminate\Foundation\Http\FormRequest;

class StoreStrataRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'model_type' => 'required|string|in:organisation,project-pitch,site,site-report,project,project-report,nursery,nursery-report',
            'model_uuid' => 'required|string',
            'description' => 'sometimes|nullable|string',
            'extent' => 'sometimes|nullable|integer|between:0,100',
        ];
    }
}
