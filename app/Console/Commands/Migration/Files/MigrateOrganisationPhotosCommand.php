<?php

namespace App\Console\Commands\Migration\Files;

use App\Models\OrganisationPhoto;
use App\Models\V2\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateOrganisationPhotosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-migrate-organisation-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates legacy organisation photos to the spatie media library';

    public function handle()
    {
        $s3BaseUrl = Storage::disk('s3')->url('/');

        OrganisationPhoto::query()
            ->each(function (OrganisationPhoto $organisationPhoto) use ($s3BaseUrl) {
                $this->info('------------');
                $this->info('Starting migration of organisation photo ' . $organisationPhoto->id);

                $target = Organisation::query()
                    ->where('id', $organisationPhoto->organisation_id)
                    ->first();

                if (! $target) {
                    $this->info('No new model found');

                    return;
                }

                /**
                 * The uploads should have been stored as paths, but they were
                 * stored as URLs, so we need to do a fix to get the path
                 */
                $filePath = substr($organisationPhoto->upload, strlen($s3BaseUrl) - 1);
                $this->info('Adding media to organisation ' .  $target->id);

                $collection = data_get($organisationPhoto, 'type', 'organisation_photo');
                $media = $target
                    ->addMediaFromDisk($filePath, 's3')
                    ->withCustomProperties([
                        'is_public' => $organisationPhoto->is_public,
                    ])
                    ->preservingOriginal()
                    ->toMediaCollection($collection, 's3');

                $media->is_public = $organisationPhoto->is_public;
                $media->file_type = 'media';
                $media->save();

                $this->info('Complete');
                $this->info('------------');
            });
    }
}
