<?php

namespace Database\Factories;

use App\Models\Programme;
use Illuminate\Database\Eloquent\Factories\Factory;

class DueSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'due_submissionable_type' => Programme::class,
            'due_submissionable_id' => Programme::factory()->create()->id,
            'due_at' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
            'is_submitted' => false,
        ];
    }
}
