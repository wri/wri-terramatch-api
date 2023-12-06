<?php

namespace App\Console\Commands\Migration\Files;

use App\Models\SubmissionMediaUpload;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateSubmissionMediaUploadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-migrate-submission-media-uploads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates legacy submission media files to the spatie media library';

    public function handle()
    {
        $s3BaseUrl = Storage::disk('s3')->url('/');

        SubmissionMediaUpload::query()
            ->each(function (SubmissionMediaUpload $submissionMediaUpload) use ($s3BaseUrl) {
                $this->info('------------');
                $this->info('Starting migration of submission media upload ' . $submissionMediaUpload->id);

                if (! is_null($submissionMediaUpload->site_submission_id)) {
                    $model = 'site report';
                    $target = SiteReport::query()
                        ->where('framework_key', 'ppc')
                        ->where('old_id', $submissionMediaUpload->site_submission_id)
                        ->first();
                } else {
                    $model = 'project report';
                    $target = ProjectReport::query()
                        ->where('framework_key', 'ppc')
                        ->where('old_id', $submissionMediaUpload->submission_id)
                        ->first();
                }

                if (! $target) {
                    $this->info('No new model found');

                    return;
                }

                /**
                 * The uploads should have been stored as paths, but they were
                 * stored as URLs, so we need to do a fix to get the path
                 */
                $filePath = substr($submissionMediaUpload->upload, strlen($s3BaseUrl) - 1);
                $this->info('Adding media to ' . $model . ' ' . $target->id);
                $media = $target
                    ->addMediaFromDisk($filePath, 's3')
                    ->preservingOriginal()
                    ->usingName($submissionMediaUpload->media_title)
                    ->toMediaCollection('media', 's3');

                $media->is_public = $submissionMediaUpload->is_public;
                $media->lat = $submissionMediaUpload->location_lat;
                $media->lng = $submissionMediaUpload->location_long;
                $media->file_type = 'media';
                $media->save();

                $this->info('Complete');
                $this->info('------------');
            });
    }
}
