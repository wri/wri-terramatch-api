<?php

namespace Database\Seeders;

use App\Models\SiteSubmissionDisturbance;
use Illuminate\Database\Seeder;

class SiteSubmissionDisturbancesTableSeeder extends Seeder
{
    public function run()
    {
        $submission = new SiteSubmissionDisturbance();
        $submission->site_submission_id = 1;
        $submission->disturbance_type = 'ecological';
        $submission->intensity = 'high';
        $submission->extent = '21-40';
        $submission->description = 'disturbance description';
        $submission->saveOrFail();

        $submission = new SiteSubmissionDisturbance();
        $submission->site_submission_id = 1;
        $submission->disturbance_type = 'ecological';
        $submission->intensity = 'low';
        $submission->extent = '21-40';
        $submission->description = 'disturbance description';
        $submission->saveOrFail();
    }
}
