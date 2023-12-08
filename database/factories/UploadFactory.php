<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UploadFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->create()->id,
            'location' => $this->faker->url() . '.png',
        ];
    }

    public function pdf()
    {
        return $this->state(function (array $attributes) {
            return [
                'location' => $this->faker->url() . '.pdf',
            ];
        });
    }

    public function tiff()
    {
        return $this->state(function (array $attributes) {
            return [
                'location' => $this->faker->url() . '.tiff',
            ];
        });
    }
}
