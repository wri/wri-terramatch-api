<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delayed_jobs', function (Blueprint $table) {
            $table->unsignedTinyInteger('progress')->nullable()->after('payload');
            $table->unsignedInteger('processed_content')->nullable()->after('progress');
            $table->unsignedInteger('total_content')->nullable()->after('processed_content');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delayed_jobs', function (Blueprint $table) {
            $table->dropColumn(['progress', 'proccessed_content', 'total_content']);
        });
    }
};
