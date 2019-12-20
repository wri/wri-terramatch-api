<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateOrganisations extends Migration
{
    public function up()
    {
        /**
         * Without temporarily stopping foreign key checks this migration will
         * not work.
         */
        DB::statement("SET FOREIGN_KEY_CHECKS = 0;");
        Schema::drop("organisations");
        Schema::create("organisations", function (Blueprint $table) {
            $table->bigIncrements("id");
        });
        /**
         * And of course we need to enable them again afterwards!
         */
        DB::statement("SET FOREIGN_KEY_CHECKS = 1;");
    }

    public function down()
    {
    }
}
