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
            'title' => 'sometimes|nullable|string|between:1,255',
            'content' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string|in:draft,published,archived',
            'author_id' => 'sometimes|nullable|exists:users,id',
            'categories' => 'sometimes|nullable|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'sometimes|nullable|array',
            'tags.*' => 'exists:tags,id',
            'published_at' => 'sometimes|nullable|date',
            'organization_id' => 'sometimes|uuid|exists:organisations,uuid',
        ];
    }
}
