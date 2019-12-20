<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMissingFieldsToPitchVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pitch_versions', function (Blueprint $table) {
            $table->longText("problem");
            $table->longText("anticipated_outcome");
            $table->longText("who_is_involved");

            $table->boolean("local_community_involvement")->default(false);
            $table->boolean("training_involved")->default(false);
            $table->text("training_type")->nullable();
            $table->integer("training_amount_people")->nullable();
            $table->mediumText("people_working_in");
            $table->integer("people_amount_nearby");
            $table->integer("people_amount_abroad");
            $table->integer("people_amount_employees");
            $table->integer("people_amount_volunteers");
            $table->integer("benefited_people");

            $table->mediumText("future_maintenance");
            $table->mediumText("use_of_resources");
            $table->string("facebook")->nullable();
            $table->string("twitter")->nullable();
            $table->string("instagram")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pitch_versions', function (Blueprint $table) {
            $table->dropColumn([
                'problem',
                'anticipated_outcome',
                'who_is_involved',
                'local_community_involvement',
                'training_involved',
                'training_type',
                'training_amount_people',
                'people_working_in',
                'people_amount_nearby',
                'people_amount_abroad',
                'people_amount_employees',
                'people_amount_volunteers',
                'benefited_people',
                'future_maintenance',
                'use_of_resources',
                'facebook',
                'twitter',
                'instagram'
            ]);
        });
    }
}
