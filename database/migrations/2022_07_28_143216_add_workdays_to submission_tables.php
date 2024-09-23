<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->integer('workdays_paid')->nullable()->after('site_id');
            $table->integer('workdays_volunteer')->nullable()->after('workdays_paid');
        });

        Schema::table('site_submissions', function (Blueprint $table) {
            $table->integer('workdays_paid')->nullable()->after('due_submission_id');
            $table->integer('workdays_volunteer')->nullable()->after('workdays_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn('workdays_paid');
            $table->dropColumn('workdays_volunteer');
        });

        Schema::table('site_submissions', function (Blueprint $table) {
            $table->dropColumn('workdays_paid');
            $table->dropColumn('workdays_volunteer');
        });
    }
};
