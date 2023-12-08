<?php

namespace Database\Seeders;

use App\Models\SiteSubmission;
use Illuminate\Database\Seeder;

class SiteSubmissionsTableSeeder extends Seeder
{
    public function run()
    {
        $submission = new SiteSubmission();
        $submission->id = 1;
        $submission->site_id = 1;
        $submission->due_submission_id = 3;
        $submission->site_submission_title = 'test title';
        $submission->disturbance_information = 'some information, about disturbances';
        $submission->public_narrative = 'some narrative';
        $submission->technical_narrative = 'some narrative';
        $submission->created_by = 'test user';
        $submission->workdays_paid = 19;
        $submission->workdays_volunteer = 22;
        $submission->created_at = now()->subMonth();
        $submission->updated_at = now()->subMonth();
        $submission->saveOrFail();

        $submission = new SiteSubmission();
        $submission->id = 2;
        $submission->site_id = 1;
        $submission->created_by = 'test user 2';
        $submission->public_narrative = null;
        $submission->technical_narrative = 'some narrative';
        $submission->workdays_volunteer = 14;
        $submission->created_at = now()->subMonth();
        $submission->updated_at = now()->subMonth();
        $submission->saveOrFail();

        $submission = new SiteSubmission();
        $submission->id = 3;
        $submission->site_id = 1;
        $submission->due_submission_id = 3;
        $submission->site_submission_title = 'test title';
        $submission->disturbance_information = 'some information, about disturbances';
        $submission->public_narrative = 'some narrative';
        $submission->technical_narrative = 'some narrative';
        $submission->workdays_paid = 6;
        $submission->workdays_volunteer = 13;
        $submission->created_by = 'test user';
        $submission->created_at = now()->subMonth();
        $submission->updated_at = now()->subMonth();
        $submission->saveOrFail();

        $submission = new SiteSubmission();
        $submission->id = 4;
        $submission->site_id = 8;
        $submission->due_submission_id = 4;
        $submission->site_submission_title = 'test title';
        $submission->disturbance_information = 'some information, about disturbances';
        $submission->public_narrative = 'some narrative';
        $submission->technical_narrative = 'some narrative';
        $submission->workdays_paid = 6;
        $submission->workdays_volunteer = 13;
        $submission->created_by = 'test user';
        $submission->created_at = now()->subMonth();
        $submission->updated_at = now()->subMonth();
        $submission->saveOrFail();
    }
}
