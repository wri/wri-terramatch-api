<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AmendOrganisationVersionsTable extends Migration
{
    public function up()
    {
        Schema::table("organisation_versions", function (Blueprint $table) {
            $table->longText("rejection_reason")->nullable();
            $table->bigInteger("approved_rejected_by")->unsigned()->nullable();
            $table->foreign("approved_rejected_by")->references("id")->on("users");
        });
        /**
         * This section can't be done using migrations. Apparently changes on
         * tables containing enums aren't supported.
         */
        DB::statement("
            ALTER TABLE `organisation_versions`
            CHANGE COLUMN `status` `status` ENUM('pending', 'approved', 'rejected', 'archived') COLLATE 'utf8mb4_unicode_ci' NOT NULL DEFAULT 'pending';
        ");
    }

    public function down()
    {
    }
}
