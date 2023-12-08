<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\JsonResource;

class FormAnswerResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'question_id' => $this['question_id'],
            'value' => $this['value'],
        ];
    }
}
