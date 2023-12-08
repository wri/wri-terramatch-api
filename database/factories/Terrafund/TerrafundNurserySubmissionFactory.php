<?php

namespace Database\Factories\Terrafund;

use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundNursery;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerrafundNurserySubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'seedlings_young_trees' => $this->faker->numberBetween(0, 65000),
            'interesting_facts' => $this->faker->paragraph(),
            'site_prep' => $this->faker->paragraph(),
            'terrafund_nursery_id' => TerrafundNursery::factory()->create()->id,
            'terrafund_due_submission_id' => TerrafundDueSubmission::factory()->create(['is_submitted' => true]),
        ];
    }
}
