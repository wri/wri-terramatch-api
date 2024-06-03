<?php

namespace App\Http\Requests\V2\Geometry;

use Illuminate\Foundation\Http\FormRequest;

class StoreGeometryRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // The rest of the validity of these will be processed within the controller. The geometries still get
        // persisted, but with codes associated as not passing certain validations. The whole request should fail
        // however if the site id is missing from any of the geometries.
        return [
            'geometries' => 'required|array',
            'geometries.*.features.*.properties.site_id' => 'required|string',
        ];
    }
}
