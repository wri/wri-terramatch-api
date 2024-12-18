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
            $table->unsignedInteger('processed_content')->nullable()->after('payload');
            $table->unsignedInteger('total_content')->nullable()->after('processed_content');
            $table->string('proccess_message')->nullable()->after('total_content');
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
            $table->dropColumn(['proccessed_content', 'total_content', 'proccess_message']);
        });
    }
};
