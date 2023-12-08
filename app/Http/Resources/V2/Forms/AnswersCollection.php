<?php

namespace App\Http\Resources\V2\Forms;

use Illuminate\Http\Resources\Json\ResourceCollection;

class AnswersCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return ['data' => FormAnswerResource::collection($this->collection)];
    }

    public static function fromArray(?array $answers = null): self
    {
        $answers = is_array($answers) ? $answers : [];

        return self::make(array_map(function ($answer) {
            if (
                (isset($answer['question_id'])) &&
                (isset($answer['value']) && is_null($answer['value']) || ! isset($answer['value'])) &&
                (isset($answer['options']) && is_null($answer['options']) || ! isset($answer['options']))
            ) {
                return [
                    'question_id' => $answer['question_id'],
                ];
            }

            if (isset($answer['value']) && ! is_null($answer['value'])) {
                return [
                    'question_id' => $answer['question_id'],
                    'value' => $answer['value'],
                ];
            }

            if (isset($answer['options']) && is_array($answer['options'])) {
                return [
                    'question_id' => $answer['question_id'],
                    'options' => $answer['options'],
                ];
            }

            return [];
        }, $answers));
    }
}
