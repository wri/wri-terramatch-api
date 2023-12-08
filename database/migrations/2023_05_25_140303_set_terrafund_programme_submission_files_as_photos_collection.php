<?php

use App\Models\Terrafund\TerrafundFile;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use Illuminate\Database\Migrations\Migration;

class SetTerrafundProgrammeSubmissionFilesAsPhotosCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        TerrafundFile::query()
            ->where('fileable_type', TerrafundProgrammeSubmission::class)
            ->update([
                'collection' => 'photos',
            ]);
    }
}
