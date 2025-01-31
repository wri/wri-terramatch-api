<?php

namespace Database\Factories\Terrafund;

use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundSite;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class TerrafundDueSubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    protected $model = TerrafundDueSubmission::class;

    public function definition()
    {
        $now = Carbon::now();
        $julyReportingPeriod = Carbon::create($now->year, 07, 31);
        $decemberReportingPeriod = Carbon::create($now->year, 12, 31);

        $now->lessThanOrEqualTo($julyReportingPeriod) ? $due_date = $julyReportingPeriod : $due_date = $decemberReportingPeriod;

        return [
            'terrafund_due_submissionable_type' => TerrafundSite::class,
            'terrafund_due_submissionable_id' => TerrafundSite::factory()->create()->id,
            'due_at' => $due_date,
            'is_submitted' => false,
            'submitted_at' => null,
        ];
    }
}
