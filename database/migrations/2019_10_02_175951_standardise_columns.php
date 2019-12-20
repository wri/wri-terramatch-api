<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class StandardiseColumns extends Migration
{
    public function up()
    {
        /**
         * This section can't be done using migrations. Apparently changes on
         * tables containing enums aren't supported.
         */
        DB::statement("
            ALTER TABLE `organisation_versions` 
            CHANGE COLUMN `rejection_reason` `rejected_reason` LONGTEXT COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL;
        ");
        /**
         * This section can't be done using migrations. Apparently changes on
         * tables containing enums aren't supported.
         */
        DB::statement("
            ALTER TABLE `organisation_document_versions` 
            CHANGE COLUMN `approved_rejected_reason` `rejected_reason` LONGTEXT COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL;
        ");
    }

    public function down()
    {
    }
}
