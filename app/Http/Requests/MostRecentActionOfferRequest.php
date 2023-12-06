<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MostRecentActionOfferRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return ['limit' => [
                'integer',
            ],];
    }
}
