<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateV2ActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_actions', function (Blueprint $table) {
            $table->string('title')->after('key')->nullable();
            $table->string('sub_title')->after('title')->nullable();
            $table->string('text')->after('sub_title')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_actions', function (Blueprint $table) {
            $table->dropColumn('title', 'sub_title', 'text');
        });
    }
}
