<?php

namespace Database\Seeders;

use App\Models\Submission;
use Illuminate\Database\Seeder;

class SubmissionsTableSeeder extends Seeder
{
    public function run()
    {
        $submission = new Submission();
        $submission->id = 1;
        $submission->programme_id = 1;
        $submission->due_submission_id = 1;
        $submission->public_narrative = 'some narrative';
        $submission->technical_narrative = 'some narrative';
        $submission->workdays_paid = 22;
        $submission->workdays_volunteer = 14;
        $submission->created_at = now()->subMonth();
        $submission->updated_at = now()->subMonth();
        $submission->saveOrFail();

        $submission = new Submission();
        $submission->id = 2;
        $submission->programme_id = 1;
        $submission->due_submission_id = 1;
        $submission->created_at = now();
        $submission->updated_at = now();
        $submission->saveOrFail();

        $submission = new Submission();
        $submission->id = 3;
        $submission->programme_id = 1;
        $submission->due_submission_id = 1;
        $submission->public_narrative = 'some other narrative';
        $submission->technical_narrative = 'some other narrative';
        $submission->workdays_paid = 9;
        $submission->workdays_volunteer = 20;
        $submission->created_at = now()->subYear();
        $submission->updated_at = now()->subYear();
        $submission->saveOrFail();
    }
}
