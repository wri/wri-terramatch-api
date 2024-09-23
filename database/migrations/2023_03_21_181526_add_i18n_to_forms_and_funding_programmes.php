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
        Schema::table('forms', function (Blueprint $table) {
            $table->integer('title_id')->nullable()->after('title');
            $table->integer('subtitle_id')->nullable()->after('subtitle');
            $table->integer('description_id')->nullable()->after('description');
        });

        Schema::table('funding_programmes', function (Blueprint $table) {
            $table->integer('name_id')->nullable()->after('name');
            $table->integer('description_id')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn('title_id', 'subtitle_id', 'description_id');
        });
        Schema::table('funding_programmes', function (Blueprint $table) {
            $table->dropColumn('name_id', 'description_id');
        });
    }
};
