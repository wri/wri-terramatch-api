<?php

namespace App\Models\V2\Forms\Casts;

use App\Http\Resources\V2\Forms\AnswersCollection;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AnswersCast implements CastsAttributes
{
    /**
     * Cast the given value.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): AnswersCollection
    {
        return AnswersCollection::fromArray(json_decode($value, true));
    }

    /**
     * Prepare the given value for storage.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (! $value instanceof AnswersCollection) {
            throw new \InvalidArgumentException('The given value is not an AnswersCollection instance.');
        }

        return json_encode($value->collection);
    }
}
