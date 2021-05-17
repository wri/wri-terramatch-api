<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeOrganisationVersionsTable extends Migration
{
    public function up()
    {
        Schema::table("organisation_versions", function (Blueprint $table) {
            $table->string("video")->nullable();
        });
        DB::statement("
            ALTER TABLE `organisation_versions`
            CHANGE COLUMN `zip_code` `zip_code` VARCHAR(255) COLLATE 'utf8mb4_unicode_ci' NULL DEFAULT NULL,
            CHANGE COLUMN `type` `type` TEXT COLLATE 'utf8mb4_unicode_ci' NOT NULL DEFAULT 'other';
        ");
    }

    public function down()
    {
    }
}
