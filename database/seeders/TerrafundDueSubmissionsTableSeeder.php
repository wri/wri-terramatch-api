<?php

namespace Database\Seeders;

use App\Models\Terrafund\TerrafundDueSubmission;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use Illuminate\Database\Seeder;

class TerrafundDueSubmissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $terrafundDueSubmission = new TerrafundDueSubmission();
        $terrafundDueSubmission->id = 1;
        $terrafundDueSubmission->terrafund_programme_id = 1;
        $terrafundDueSubmission->terrafund_due_submissionable_type = TerrafundSite::class;
        $terrafundDueSubmission->terrafund_due_submissionable_id = 1;
        $terrafundDueSubmission->due_at = now()->addMonth()->startOfDay();
        $terrafundDueSubmission->saveOrFail();

        $terrafundDueSubmission = new TerrafundDueSubmission();
        $terrafundDueSubmission->id = 2;
        $terrafundDueSubmission->terrafund_programme_id = 1;
        $terrafundDueSubmission->terrafund_due_submissionable_type = TerrafundNursery::class;
        $terrafundDueSubmission->terrafund_due_submissionable_id = 1;
        $terrafundDueSubmission->due_at = now()->addMonth()->startOfDay();
        $terrafundDueSubmission->saveOrFail();

        $terrafundDueSubmission = new TerrafundDueSubmission();
        $terrafundDueSubmission->id = 3;
        $terrafundDueSubmission->terrafund_programme_id = 1;
        $terrafundDueSubmission->terrafund_due_submissionable_type = TerrafundSite::class;
        $terrafundDueSubmission->terrafund_due_submissionable_id = 1;
        $terrafundDueSubmission->due_at = now()->addMonth()->startOfDay();
        $terrafundDueSubmission->saveOrFail();

        $terrafundDueSubmission = new TerrafundDueSubmission();
        $terrafundDueSubmission->id = 4;
        $terrafundDueSubmission->terrafund_programme_id = 1;
        $terrafundDueSubmission->terrafund_due_submissionable_type = TerrafundNursery::class;
        $terrafundDueSubmission->terrafund_due_submissionable_id = 1;
        $terrafundDueSubmission->due_at = now()->subMonth();
        $terrafundDueSubmission->is_submitted = true;
        $terrafundDueSubmission->saveOrFail();

        $terrafundDueSubmission = new TerrafundDueSubmission();
        $terrafundDueSubmission->id = 5;
        $terrafundDueSubmission->terrafund_programme_id = 1;
        $terrafundDueSubmission->terrafund_due_submissionable_type = TerrafundSite::class;
        $terrafundDueSubmission->terrafund_due_submissionable_id = 1;
        $terrafundDueSubmission->is_submitted = true;
        $terrafundDueSubmission->due_at = now()->subMonth();
        $terrafundDueSubmission->saveOrFail();

        $terrafundDueSubmission = new TerrafundDueSubmission();
        $terrafundDueSubmission->id = 6;
        $terrafundDueSubmission->terrafund_programme_id = 1;
        $terrafundDueSubmission->terrafund_due_submissionable_type = TerrafundProgramme::class;
        $terrafundDueSubmission->terrafund_due_submissionable_id = 1;
        $terrafundDueSubmission->is_submitted = false;
        $terrafundDueSubmission->due_at = '2022-10-06 00:00:00';
        $terrafundDueSubmission->saveOrFail();
    }
}
