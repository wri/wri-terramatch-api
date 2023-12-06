<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2ApplicationsTable extends Migration
{
    public function up()
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('funding_programme_uuid', )->nullable();
            $table->string('organisation_uuid')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('stages', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->string('stage_uuid')->nullable()->after('form_id');
            $table->integer('application_id')->nullable()->after('stage_uuid');
        });
    }

    public function down()
    {
        Schema::drop('applications');

        Schema::table('stages', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn('application_id');
        });
    }
}
