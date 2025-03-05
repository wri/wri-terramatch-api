<?php

namespace App\Http\Requests\V2\ImpactStories;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImpactStoryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'title' => 'sometimes|nullable|string|max:71',
            'date' => 'sometimes|nullable|date',
            'category' => 'sometimes|nullable|array',
            'thumbnail' => 'sometimes|nullable|string',
            'content' => 'sometimes|nullable|json',
            'status' => 'required|string|in:draft,published',
        ];
    }
}
