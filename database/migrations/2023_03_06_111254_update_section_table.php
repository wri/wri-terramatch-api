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
        Schema::table('form_sections', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('title_id');
            $table->string('subtitle_id')->nullable()->after('subtitle');
            $table->text('description')->change();
        });

        Schema::table('form_questions', function (Blueprint $table) {
            $table->text('description')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('form_sections', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'subtitle_id']);
        });
    }
};
