<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddRejectedReasonBody extends Migration
{
    public function up()
    {
        $tables = [
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
                $table->longText("rejected_reason_body")->default("");
            });
            DB::statement("UPDATE " . $table . " SET rejected_reason_body = rejected_reason WHERE status = 'rejected';");
            DB::statement("UPDATE " . $table . " SET rejected_reason = 'other' WHERE status = 'rejected';");
        }
    }

    public function down()
    {
    }
}
