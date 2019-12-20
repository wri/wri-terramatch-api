<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RemoveTimestamps extends Migration
{
    public function up()
    {
        Schema::table("organisation_versions", function (Blueprint $table) {
            $table->dropTimestamps();
        });
        Schema::table("organisation_document_versions", function (Blueprint $table) {
            $table->dropTimestamps();
        });
        Schema::table("organisation_versions", function (Blueprint $table) {
            $table->timestamp("approved_rejected_at")->nullable();
        });
    }

    public function down()
    {
    }
}
