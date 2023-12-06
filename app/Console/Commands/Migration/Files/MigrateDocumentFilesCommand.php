<?php

namespace App\Console\Commands\Migration\Files;

use App\Models\DocumentFile;
use App\Models\Programme;
use App\Models\Site;
use App\Models\SiteSubmission;
use App\Models\Submission;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site as V2Site;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateDocumentFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-migrate-document-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates legacy document files to the spatie media library';

    public function handle()
    {
        $s3BaseUrl = Storage::disk('s3')->url('/');

        DocumentFile::query()
            ->each(function (DocumentFile $documentFile) use ($s3BaseUrl) {
                $this->info('------------');
                $this->info('Starting migration of document file ' . $documentFile->id);

                switch ($documentFile->document_fileable_type) {
                    case Site::class:
                        $model = 'site';
                        $query = V2Site::query();

                        break;
                    case Programme::class:
                        $model = 'project';
                        $query = Project::query();

                        break;
                    case SiteSubmission::class:
                        $model = 'site report';
                        $query = SiteReport::query();

                        break;
                    case Submission::class:
                        $model = 'project report';
                        $query = ProjectReport::query();

                        break;
                }

                $target = $query
                    ->where('framework_key', 'ppc')
                    ->where('old_id', $documentFile->document_fileable_id)
                    ->first();

                if (! $target) {
                    $this->info('No new model found');

                    return;
                }

                /**
                 * The uploads should have been stored as paths, but they were
                 * stored as URLs, so we need to do a fix to get the path
                 */
                $filePath = substr($documentFile->upload, strlen($s3BaseUrl) - 1);
                $this->info('Adding media to ' . $model . ' ' . $target->id);

                $collection = data_get($documentFile, 'collection', 'document_file');
                $media = $target
                    ->addMediaFromDisk($filePath, 's3')
                    ->preservingOriginal()
                    ->usingName($documentFile->title)
                    ->toMediaCollection($collection, 's3');

                $media->is_public = $documentFile->is_public;
                $media->file_type = 'documents';
                $media->save();

                $this->info('Complete');
                $this->info('------------');
            });
    }
}
