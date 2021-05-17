<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateVisibilityMode extends Migration
{

    public function up()
    {
        $visibilities = [
            "archived",
            "looking",
            "talking",
            "entering_contracts",
            "partially_funded",
            "fully_funded",
            "finished"
        ];
        Schema::table("offers", function (Blueprint $table) use ($visibilities) {
            $table->enum("visibility", $visibilities)->default("looking");
        });
        Schema::table("pitches", function (Blueprint $table) use ($visibilities) {
            $table->enum("visibility", $visibilities)->default("looking");
        });
        DB::statement("UPDATE offers SET visibility = 'looking' WHERE completed = 0;");
        DB::statement("UPDATE offers SET visibility = 'archived' WHERE completed = 1 AND successful = 0;");
        DB::statement("UPDATE offers SET visibility = 'fully_funded' WHERE completed = 1 AND successful = 1;");
        DB::statement("UPDATE pitches SET visibility = 'looking' WHERE completed = 0;");
        DB::statement("UPDATE pitches SET visibility = 'archived' WHERE completed = 1 AND successful = 0;");
        DB::statement("UPDATE pitches SET visibility = 'fully_funded' WHERE completed = 1 AND successful = 1;");
        Schema::table("offers", function (Blueprint $table) use ($visibilities) {
            $table->dropColumn("completed");
            $table->dropColumn("successful");
            $table->dropForeign("offers_completed_by_foreign");
            $table->dropColumn("completed_by");
            $table->dropColumn("completed_at");
        });
        Schema::table("pitches", function (Blueprint $table) use ($visibilities) {
            $table->dropColumn("completed");
            $table->dropColumn("successful");
            $table->dropForeign("pitches_completed_by_foreign");
            $table->dropColumn("completed_by");
            $table->dropColumn("completed_at");
        });
    }

    public function down()
    {
    }
}
