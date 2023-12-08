<?php

namespace App\Http\Requests\V2\TreeSpecies;

use App\Models\V2\TreeSpecies\TreeSpecies;
use Illuminate\Foundation\Http\FormRequest;

class StoreTreeSpeciesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'model_type' => 'required|string|in:organisation,project-pitch,site,site-report,project,project-report,nursery,nursery-report',
            'model_uuid' => 'required|string',
            'name' => 'sometimes|nullable|string|between:1,255',
            'amount' => 'sometimes|nullable|integer|between:0,2147483647',
            'type' => 'sometimes|nullable|string',
            'collection' => 'sometimes|nullable|string|in:' . implode(',', array_keys(TreeSpecies::$collections)),
        ];
    }
}
