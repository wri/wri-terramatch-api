<?php

namespace Database\Factories;

use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class SiteSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'site_id' => Site::factory()->create(),
            'created_by' => $this->faker->name(),

//            'disturbance_information' => $this->faker->text(300),
//            'technical_narrative' => $this->faker->text(300),
//            'public_narrative' => $this->faker->text(300),
//            'workdays_paid' => $this->faker->numberBetween(0, 200),
//            'workdays_volunteer' => $this->faker->numberBetween(0, 200),
        ];
    }
}
