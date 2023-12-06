<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('site_id');
            $table->integer('approved_by')->nullable()->after('approved_at');
        });

        Schema::table('site_submissions', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('due_submission_id');
            $table->integer('approved_by')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn([
                'approved_at',
                'approved_by',
            ]);
        });

        Schema::table('site_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'approved_at',
                'approved_by',
            ]);
        });
    }
};
