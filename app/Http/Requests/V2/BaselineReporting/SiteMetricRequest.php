<?php

namespace App\Http\Requests\V2\BaselineReporting;

use Illuminate\Foundation\Http\FormRequest;

class SiteMetricRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'uuid' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'monitorable_type' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'monitorable_id' => [
                'sometimes',
                'nullable',
                'integer',
            ],
            'tree_count' => [
                'sometimes',
                'nullable',
                'numeric',
            ],
            'tree_cover' => [
                'sometimes',
                'nullable',
                'numeric',
                'max:100',
            ],

            'field_tree_count' => [
                'sometimes',
                'nullable',
                'numeric',
            ],
        ];
    }
}
