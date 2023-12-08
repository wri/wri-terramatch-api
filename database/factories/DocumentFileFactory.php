<?php

namespace Database\Factories;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid,
            'document_fileable_type' => Programme::class,
            'document_fileable_id' => 1,
            'upload' => $this->faker->imageUrl(10, 10),
            'title' => $this->faker->word,
            'collection' => 'logo',
            'is_public' => true,
        ];
    }
}
