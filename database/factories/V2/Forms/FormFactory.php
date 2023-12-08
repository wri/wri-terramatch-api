<?php

namespace Database\Factories\V2\Forms;

use App\Models\V2\Stages\Stage;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'subtitle' => $this->faker->sentence(),
            'description' => $this->faker->sentence(),
            'documentation' => $this->faker->url(),
            'documentation_label' => $this->faker->word(),
            'submission_message' => $this->faker->sentence(),
            'duration' => $this->faker->numberBetween(1, 5),
            'deadline_at' => $this->faker->dateTime(now()->addCenturies(2)),
            'published' => $this->faker->boolean(),
            'stage_id' => Stage::factory()->create()->uuid,
            'updated_by' => User::factory()->create()->uuid,
        ];
    }

    public function unpublished()
    {
        return $this->state(function (array $attributes) {
            return [
                'published' => false,
            ];
        });
    }

    public function published()
    {
        return $this->state(function (array $attributes) {
            return [
                'published' => true,
            ];
        });
    }
}
