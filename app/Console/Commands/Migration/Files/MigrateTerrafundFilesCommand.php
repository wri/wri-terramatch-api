<?php

namespace App\Console\Commands\Migration\Files;

use App\Models\Terrafund\TerrafundFile;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateTerrafundFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-migrate-terrafund-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates legacy terrafund files to the spatie media library';

    public function handle()
    {
        $s3BaseUrl = Storage::disk('s3')->url('/');

        TerrafundFile::query()
            ->each(function (TerrafundFile $terrafundFile) use ($s3BaseUrl) {
                $this->info('------------');
                $this->info('Starting migration of terrafund file ' . $terrafundFile->id);

                switch ($terrafundFile->fileable_type) {
                    case TerrafundProgramme::class:
                        $model = 'project';
                        $query = Project::query();

                        break;
                    case TerrafundSite::class:
                        $model = 'site';
                        $query = Site::query();

                        break;
                    case TerrafundNursery::class:
                        $model = 'nursery';
                        $query = Nursery::query();

                        break;
                    case TerrafundNurserySubmission::class:
                        $model = 'nursery report';
                        $query = NurseryReport::query();

                        break;
                    case TerrafundProgrammeSubmission::class:
                        $model = 'project report';
                        $query = ProjectReport::query();

                        break;
                    case TerrafundSiteSubmission::class:
                        $model = 'site report';
                        $query = SiteReport::query();

                        break;
                }

                $target = $query
                    ->where('framework_key', 'terrafund')
                    ->where('old_id', $terrafundFile->fileable_id)
                    ->first();

                if (! $target) {
                    $this->info('No new model found');

                    return;
                }

                /**
                 * The uploads should have been stored as paths, but they were
                 * stored as URLs, so we need to do a fix to get the path
                 */
                $filePath = substr($terrafundFile->upload, strlen($s3BaseUrl) - 1);
                $this->info('Adding media to ' . $model . ' ' . $target->id);

                $collection = data_get($terrafundFile, 'collection', 'file');
                $media = $target
                    ->addMediaFromDisk($filePath, 's3')
                    ->preservingOriginal()
                    ->toMediaCollection($collection, 's3');

                $media->is_public = $terrafundFile->is_public;
                $media->lat = $terrafundFile->location_lat;
                $media->lng = $terrafundFile->location_long;
                $media->file_type = 'media';
                $media->save();

                $this->info('Complete');
                $this->info('------------');
            });
    }
}
