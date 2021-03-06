<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToVersions extends Migration
{
    public function up()
    {
        $tables = [
            "carbon_certifications",
            "organisation_documents", 
            "pitch_documents",
            "restoration_method_metrics",
            "tree_species",
            "carbon_certification_versions",
            "organisation_document_versions",
            "organisation_versions",
            "pitch_document_versions",
            "pitch_versions",
            "restoration_method_metric_versions",
            "tree_species_versions"
        ];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->timestamps();
            });
        }
    }

    public function down()
    {
    }
}
