<?php

namespace App\Http\Requests\V2\Disturbances;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisturbanceRequest extends FormRequest
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
            'type' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'intensity' => 'sometimes|nullable|string',
            'extent' => 'sometimes|nullable|string',
            'collection' => 'sometimes|nullable|string',
        ];
    }
}
