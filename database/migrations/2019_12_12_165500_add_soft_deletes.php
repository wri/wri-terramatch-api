<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeletes extends Migration
{
    public function up()
    {
        Schema::table("organisation_documents", function(Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("organisation_document_versions", function(Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("pitch_documents", function(Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table("pitch_document_versions", function(Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
    }
}
