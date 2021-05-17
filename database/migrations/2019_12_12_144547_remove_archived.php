<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveArchived extends Migration
{
    public function up()
    {
        Schema::table("pitches", function(Blueprint $table) {
            $table->dropForeign("pitches_archived_by_foreign");
            $table->dropColumn("archived");
            $table->dropColumn("archived_by");
            $table->dropColumn("archived_at");
        });
    }

    public function down()
    {
    }
}
