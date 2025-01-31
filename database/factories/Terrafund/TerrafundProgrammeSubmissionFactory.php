<?php

namespace Database\Factories\Terrafund;

use App\Models\Terrafund\TerrafundDueSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerrafundProgrammeSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'landscape_community_contribution' => $this->faker->paragraph(),
            'top_three_successes' => $this->faker->paragraph(),
            'challenges_and_lessons' => $this->faker->paragraph(),
            'maintenance_and_monitoring_activities' => $this->faker->paragraph(),
            'significant_change' => $this->faker->paragraph(),
            'percentage_survival_to_date' => $this->faker->numberBetween(0, 100),
            'survival_calculation' => $this->faker->sentence(),
            'survival_comparison' => $this->faker->sentence(),
            'ft_women' => $this->faker->numberBetween(0, 4000000000),
            'ft_men' => $this->faker->numberBetween(0, 4000000000),
            'ft_youth' => $this->faker->numberBetween(0, 4000000000),
            'ft_total' => $this->faker->numberBetween(0, 4000000000),
            'pt_women' => $this->faker->numberBetween(0, 4000000000),
            'pt_men' => $this->faker->numberBetween(0, 4000000000),
            'pt_youth' => $this->faker->numberBetween(0, 4000000000),
            'pt_total' => $this->faker->numberBetween(0, 4000000000),
            'volunteer_women' => $this->faker->numberBetween(0, 4000000000),
            'volunteer_men' => $this->faker->numberBetween(0, 4000000000),
            'volunteer_youth' => $this->faker->numberBetween(0, 4000000000),
            'volunteer_total' => $this->faker->numberBetween(0, 4000000000),
            'people_annual_income_increased' => $this->faker->numberBetween(0, 4000000000),
            'people_knowledge_skills_increased' => $this->faker->numberBetween(0, 4000000000),
            'terrafund_due_submission_id' => TerrafundDueSubmission::factory()->create(['is_submitted' => true]),
        ];
    }
}
