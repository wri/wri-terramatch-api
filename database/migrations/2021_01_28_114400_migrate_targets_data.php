<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class MigrateTargetsData extends Migration
{
    public function up()
    {
        $columns = [
            "trees_planted",
            "non_trees_planted",
            "survival_rate",
            "land_size_planted",
            "land_size_restored",
            "carbon_captured",
            "supported_nurseries",
            "nurseries_production_amount",
            "short_term_jobs_amount",
            "long_term_jobs_amount",
            "volunteers_amount",
            "training_amount",
            "benefited_people"
        ];
        $targets = DB::table("targets")->get();
        foreach ($targets as $target) {
            $data = [];
            foreach ($columns as $column) {
                if (!is_null($target->$column)) {
                    $data[$column] = $target->$column;
                }
            }
            DB::table("targets")->where("id", "=", $target->id)->update(["data" => json_encode($data)]);
        }
        foreach ($columns as $column) {
            Schema::table("targets", function (Blueprint $table) use ($column) {
                $table->dropColumn($column);
            });
        }
    }

    public function down()
    {
    }
}
