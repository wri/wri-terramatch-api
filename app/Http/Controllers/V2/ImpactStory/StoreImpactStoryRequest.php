<?php

namespace App\Http\Requests\V2\ImpactStory;

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
            'organization_id' => 'required|exists:organisations,id',
            'date' => 'required|date',
            'category' => 'required|json',
            'thumbnail' => 'required|string',
            'content' => 'required|json',
        ];
    }
}
