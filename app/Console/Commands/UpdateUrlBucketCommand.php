<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateUrlBucketCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-url-bucket {oldBucketName} {newBucketName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update bucket url for uploaded files';

    public function handle(): int
    {
        $oldBucketName = $this->argument('oldBucketName');
        $newBucketName = $this->argument('newBucketName');

        $this->updateTable('document_files', 'upload', $oldBucketName, $newBucketName);
        $this->updateTable('media_uploads', 'upload', $oldBucketName, $newBucketName);
        $this->updateTable('offers', 'cover_photo', $oldBucketName, $newBucketName);
        $this->updateTable('organisation_document_versions', 'document', $oldBucketName, $newBucketName);
        $this->updateTable('organisation_files', 'upload', $oldBucketName, $newBucketName);
        $this->updateTable('organisation_photos', 'upload', $oldBucketName, $newBucketName);
        $this->updateTable('organisation_versions', 'avatar', $oldBucketName, $newBucketName);
        $this->updateTable('organisation_versions', 'cover_photo', $oldBucketName, $newBucketName);
        $this->updateTable('pitch_document_versions', 'document', $oldBucketName, $newBucketName);
        $this->updateTable('pitch_versions', 'cover_photo', $oldBucketName, $newBucketName);
        $this->updateTable('pitch_versions', 'video', $oldBucketName, $newBucketName);
        $this->updateTable('programmes', 'thumbnail', $oldBucketName, $newBucketName);
        $this->updateTable('satellite_monitors', 'map', $oldBucketName, $newBucketName);
        $this->updateTable('sites', 'stratification_for_heterogeneity', $oldBucketName, $newBucketName);
        $this->updateTable('socioeconomic_benefits', 'upload', $oldBucketName, $newBucketName);
        $this->updateTable('submission_media_uploads', 'upload', $oldBucketName, $newBucketName);
        $this->updateTable('terrafund_files', 'upload', $oldBucketName, $newBucketName);
        $this->updateTable('uploads', 'location', $oldBucketName, $newBucketName);
        $this->updateTable('users', 'avatar', $oldBucketName, $newBucketName);

        // Add more tables if needed

        $this->info('Update completed successfully.');

        return 0;
    }

    /**
     * Update the 'upload' column for a specific table.
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $oldBucketName
     * @param string $newBucketName
     */
    private function updateTable(string $tableName, string $columnName, string $oldBucketName, string $newBucketName)
    {
        if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, $columnName)) {
            $records = DB::table($tableName)->where($columnName, 'like', '%' . $oldBucketName . '%')->get();

            foreach ($records as $record) {
                $originalValue = $record->$columnName;
                $newValue = str_replace($oldBucketName, $newBucketName, $originalValue);

                DB::table($tableName)->where('id', $record->id)->update([$columnName => $newValue]);

            }
        } else {
            $this->warn("Table $tableName or column $columnName not found. Skipped.");
        }
    }
}
