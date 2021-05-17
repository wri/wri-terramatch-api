<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTimestampToVisibilities extends Migration
{

    public function up()
    {
        foreach (["offers", "pitches"] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->timestamp("visibility_updated_at")->nullable();
            });
            DB::statement("
                UPDATE `" . $table . "` SET `visibility_updated_at` = `created_at`;
            ");
        }
    }

    public function down()
    {
    }
}
