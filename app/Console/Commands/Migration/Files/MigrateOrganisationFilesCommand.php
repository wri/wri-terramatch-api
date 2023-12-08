<?php

namespace App\Console\Commands\Migration\Files;

use App\Models\OrganisationFile;
use App\Models\V2\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateOrganisationFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-migrate-organisation-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates legacy organisation files to the spatie media library';

    public function handle()
    {
        $s3BaseUrl = Storage::disk('s3')->url('/');

        OrganisationFile::query()
            ->each(function (OrganisationFile $organisationFile) use ($s3BaseUrl) {
                $this->info('------------');
                $this->info('Starting migration of organisation file ' . $organisationFile->id);

                $target = Organisation::query()
                    ->where('id', $organisationFile->organisation_id)
                    ->first();

                if (! $target) {
                    $this->info('No new model found');

                    return;
                }

                /**
                 * The uploads should have been stored as paths, but they were
                 * stored as URLs, so we need to do a fix to get the path
                 */
                $filePath = substr($organisationFile->upload, strlen($s3BaseUrl) - 1);
                $this->info('Adding media to organisation ' .  $target->id);

                $collection = data_get($organisationFile, 'type', 'organisation_file');
                $media = $target
                    ->addMediaFromDisk($filePath, 's3')
                    ->preservingOriginal()
                    ->toMediaCollection($collection, 's3');

                $media->file_type = 'documents';
                $media->save();

                $this->info('Complete');
                $this->info('------------');
            });
    }
}
