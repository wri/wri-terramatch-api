<?php

namespace App\Console\Commands;

use App\Models\SiteSubmission;
use App\Models\SiteSubmissionDisturbance;
use App\Models\SiteTreeSpecies;
use App\Models\SocioeconomicBenefit;
use App\Models\SubmissionMediaUpload;
use DatabaseSeeder;
use Illuminate\Console\Command;

class AddTestDataForSiteSubmissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site-submissions:add-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add test data for the site submissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // create the site submission
        $submission = new SiteSubmission();
        $submission->site_id = 1;
        $submission->site_submission_title = 'command test title';
        $submission->disturbance_information = "some information, about disturbances that's disturbing";
        $submission->created_by = 'test user 42';
        $submission->created_at = now()->subMonth();
        $submission->updated_at = now()->subMonth();
        $submission->saveOrFail();

        // create tree species
        $siteTreeSpecies = new SiteTreeSpecies();
        $siteTreeSpecies->site_submission_id = $submission->id;
        $siteTreeSpecies->site_id = 1;
        $siteTreeSpecies->amount = 250;
        $siteTreeSpecies->name = 'A tree species';
        $siteTreeSpecies->saveOrFail();

        // then disturbances
        $disturbance = new SiteSubmissionDisturbance();
        $disturbance->site_submission_id = $submission->id;
        $disturbance->disturbance_type = 'ecological';
        $disturbance->intensity = 'high';
        $disturbance->extent = '0-20';
        $disturbance->description = 'disturbance description 65';
        $disturbance->saveOrFail();

        $disturbance2 = new SiteSubmissionDisturbance();
        $disturbance2->site_submission_id = $submission->id;
        $disturbance2->extent = '0-20';
        $disturbance2->intensity = 'high';
        $disturbance2->description = 'disturbance description 23';
        $disturbance2->saveOrFail();

        // then socio
        $benefit = new SocioeconomicBenefit();
        $benefit->upload = DatabaseSeeder::seedRandomObject('file');
        $benefit->name = 'test name command';
        $benefit->site_id = 1;
        $benefit->site_submission_id = $submission->id;
        $benefit->saveOrFail();

        // then media
        $media = new SubmissionMediaUpload();
        $media->media_title = 'test media upload for command';
        $media->is_public = true;
        $media->submission_id = null;
        $media->site_submission_id = $submission->id;
        $media->upload = DatabaseSeeder::seedRandomObject('image');
        $media->saveOrFail();

        $media = new SubmissionMediaUpload();
        $media->media_title = 'test media upload for command 2';
        $media->is_public = true;
        $media->submission_id = null;
        $media->site_submission_id = $submission->id;
        $media->upload = DatabaseSeeder::seedRandomObject('file');
        $media->saveOrFail();

        return 0;
    }
}
