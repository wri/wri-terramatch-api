<?php

namespace App\Http\Requests\V2\Polygons;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusPolygonsUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'comment' => 'sometimes|nullable|string',
            'updatePolygons' => 'sometimes|nullable|array',
            'updatePolygons.*.uuid' => 'sometimes|nullable|string',
            'updatePolygons.*.status' => 'sometimes|nullable|string', 
        ];
    }
}
