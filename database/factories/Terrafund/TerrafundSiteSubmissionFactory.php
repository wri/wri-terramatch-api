<?php

namespace Database\Factories\Terrafund;

use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundSite;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerrafundSiteSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'terrafund_site_id' => TerrafundSite::factory()->create(),
            'terrafund_due_submission_id' => TerrafundDueSubmission::factory()->create(['is_submitted' => true]),
        ];
    }
}
