<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class StandardiseColumns extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE `organisation_versions` 
            CHANGE COLUMN `rejection_reason` `rejected_reason` LONGTEXT COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL;
        ");
        DB::statement("
            ALTER TABLE `organisation_document_versions` 
            CHANGE COLUMN `approved_rejected_reason` `rejected_reason` LONGTEXT COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL;
        ");
    }

    public function down()
    {
    }
}
