<?php

namespace App\Http\Requests\V2\Stages;

use App\Models\V2\Stages\Stage;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStageStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status' => ['string', 'in:' . implode(',', array_keys(Stage::$statuses))],
        ];
    }
}
