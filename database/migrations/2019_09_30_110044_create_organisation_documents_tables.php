<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateOrganisationDocumentsTables extends Migration
{
    public function up()
    {
        Schema::create("organisation_documents", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("organisation_id")->unsigned();
            $table->foreign("organisation_id")->references("id")->on("organisations");
        });
        Schema::create("organisation_document_versions", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("organisation_document_id")->unsigned();
            $table->string("name");
            $table->string("type");
            $table->string("document");
            $table->string("status");
            $table->timestamps();
            $table->string("approved_rejected_by")->nullable();
            $table->timestamp("approved_rejected_at")->nullable();
            $table->string("approved_rejected_reason")->nullable();
            $table->foreign("organisation_document_id")->references("id")->on("organisation_documents");
        });
    }

    public function down()
    {
    }
}
