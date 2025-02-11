<?php

namespace App\Http\Requests\V2\ImpactStories;

use Illuminate\Foundation\Http\FormRequest;

class StoreImpactStoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:71',
            'status' => 'required|string',
            'organization_id' => 'exists:organisations,id',
            'date' => 'date',
            'category' => 'json',
            'thumbnail' => 'string',
            'content' => 'json',
        ];
    }
}
