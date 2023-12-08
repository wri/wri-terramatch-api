<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyFormSubmissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->text('feedback')->change();
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->renameColumn('leadership_team', 'leadership_team_txt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_submissions', function (Blueprint $table) {
            $table->string('feedback')->change();
        });

        Schema::table('organisations', function (Blueprint $table) {
            $table->renameColumn('leadership_team_txt', 'leadership_team');
        });
    }
}
