<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class TidyUpTables extends Migration
{
    public function up()
    {
        /**
         * This section can't be done using migrations. Apparently changes on
         * tables containing enums aren't supported.
         */
        DB::statement("
            ALTER TABLE `organisation_document_versions` 
            CHANGE COLUMN `status` `status` ENUM('pending', 'archived', 'approved', 'rejected') COLLATE 'utf8mb4_unicode_ci' NOT NULL DEFAULT 'pending';
        ");
        Schema::table("offers", function (Blueprint $table) {
            $table->integer("funding_amount")->change();
        });
        Schema::table("pitch_versions", function (Blueprint $table) {
            $table->dropTimestamps();
        });
        Schema::table("pitches", function (Blueprint $table) {
            $table->timestamps();
        });
    }

    public function down()
    {
    }
}
