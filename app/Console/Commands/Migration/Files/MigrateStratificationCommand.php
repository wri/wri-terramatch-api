<?php

namespace App\Console\Commands\Migration\Files;

use App\Models\Site as PPCSite;
use App\Models\V2\Sites\Site;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateStratificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-migrate-stratification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates legacy stratification for heterogeneity files to the spatie media library';

    public function handle()
    {
        $s3BaseUrl = Storage::disk('s3')->url('/');

        PPCSite::query()
            ->each(function (PPCSite $site) use ($s3BaseUrl) {
                $this->info('------------');
                $this->info('Starting migration of stratification file ' . $site->id);

                $target = Site::where('old_id', $site->id)
                    ->where('old_model', PPCSite::class)
                    ->first();

                if (! $target) {
                    $this->info('No new model found');

                    return;
                }

                /**
                 * The uploads should have been stored as paths, but they were
                 * stored as URLs, so we need to do a fix to get the path
                 */
                $filePath = substr($site->stratification_for_heterogeneity, strlen($s3BaseUrl) - 1);
                $this->info('Adding media to Site ' . $site->id);

                $media = $target
                    ->addMediaFromDisk($filePath, 's3')
                    ->preservingOriginal()
                    ->toMediaCollection('stratification_for_heterogeneity', 's3');

                $media->file_type = 'documents';
                $media->save();

                $this->info('Complete');
                $this->info('------------');
            });
    }
}
