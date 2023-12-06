<?php

namespace App\Console\Commands\Migration\Files;

use App\Models\SocioeconomicBenefit;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateSocioeconomicBenefitsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-migrate-socioeconomic-benefits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates legacy socioeconomic files to the spatie media library';

    public function handle()
    {
        $s3BaseUrl = Storage::disk('s3')->url('/');

        SocioeconomicBenefit::query()
            ->each(function (SocioeconomicBenefit $socioeconomicBenefit) use ($s3BaseUrl) {
                $this->info('------------');
                $this->info('Starting migration of socioeconomic file ' . $socioeconomicBenefit->id);

                /**
                 * this should have been polymorphic, but isn't
                 * so we have to do a horrible set of if statements
                 * starting with submissions
                 */
                if (! is_null($socioeconomicBenefit->site_submission_id)) {
                    $model = 'site report';
                    $query = SiteReport::query();
                }

                if (is_null($socioeconomicBenefit->site_submission_id) && ! is_null($socioeconomicBenefit->site_id)) {
                    $model = 'site';
                    $query = Site::query();
                }

                if (! is_null($socioeconomicBenefit->programme_submission_id)) {
                    $model = 'project report';
                    $query = ProjectReport::query();
                }

                if (is_null($socioeconomicBenefit->programme_submission_id) && ! is_null($socioeconomicBenefit->programme_id)) {
                    $model = 'project';
                    $query = Project::query();
                }

                $target = $query
                    ->where('framework_key', 'ppc')
                    ->where('old_id', $socioeconomicBenefit->document_fileable_id)
                    ->first();

                if (! $target) {
                    $this->info('No new model found');

                    return;
                }

                /**
                 * The uploads should have been stored as paths, but they were
                 * stored as URLs, so we need to do a fix to get the path
                 */
                $filePath = substr($socioeconomicBenefit->upload, strlen($s3BaseUrl) - 1);
                $this->info('Adding media to ' . $model . ' ' . $target->id);

                $media = $target
                    ->addMediaFromDisk($filePath, 's3')
                    ->preservingOriginal()
                    ->usingName($socioeconomicBenefit->name)
                    ->toMediaCollection('socioeconomic_benefits', 's3');

                $media->file_type = 'documents';
                $media->save();

                $this->info('Complete');
                $this->info('------------');
            });
    }
}
