<?php

namespace Database\Factories\V2\Forms;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FormCommonOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $label = $this->faker->words(3, true);

        return [
            'uuid' => $this->faker->uuid,
            'bucket' => $this->faker->word,
            'slug' => Str::slug($label),
            'alt_value' => null,
            'label' => $label,
        ];
    }
}
