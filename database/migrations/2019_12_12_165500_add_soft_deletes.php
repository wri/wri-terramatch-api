<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
