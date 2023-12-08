<?php

namespace App\Console\Commands\Migration\Files;

use App\Models\MediaUpload;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateMediaUploadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-migrate-media-uploads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates legacy media files to the spatie media library';

    public function handle()
    {
        $s3BaseUrl = Storage::disk('s3')->url('/');

        MediaUpload::query()
            ->each(function (MediaUpload $mediaUpload) use ($s3BaseUrl) {
                $this->info('------------');
                $this->info('Starting migration of media upload ' . $mediaUpload->id);

                if (! is_null($mediaUpload->programme_id)) {
                    $model = 'project';
                    $target = Project::query()
                        ->where('old_id', $mediaUpload->programme_id)
                        ->where('framework_key', 'ppc')
                        ->first();
                } else {
                    $model = 'site';
                    $target = Site::query()
                        ->where('old_id', $mediaUpload->site_id)
                        ->where('framework_key', 'ppc')
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
                $filePath = substr($mediaUpload->upload, strlen($s3BaseUrl) - 1);
                $this->info('Adding media to ' . $model . ' ' . $target->id);
                $media = $target
                    ->addMediaFromDisk($filePath, 's3')
                    ->preservingOriginal()
                    ->toMediaCollection('media', 's3');

                $media->is_public = $mediaUpload->is_public;
                $media->lat = $mediaUpload->location_lat;
                $media->lng = $mediaUpload->location_long;
                $media->file_type = 'media';
                $media->save();

                $this->info('Complete');
                $this->info('------------');
            });
    }
}
