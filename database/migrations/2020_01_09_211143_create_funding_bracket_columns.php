<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateFundingBracketColumns extends Migration
{
    public function up()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string("funding_bracket")->default("lt_50k");
        });
        DB::statement("
            ALTER TABLE `offers` 
            CHANGE COLUMN `funding_amount` `funding_amount` INT(11) NULL DEFAULT NULL;
        ");
        Schema::table('pitch_versions', function (Blueprint $table) {
            $table->string("funding_bracket")->default("lt_50k");
        });
        DB::statement("
            ALTER TABLE `pitch_versions` 
            CHANGE COLUMN `funding_amount` `funding_amount` INT(11) NULL DEFAULT NULL;
        ");
    }

    public function down()
    {
    }
}
