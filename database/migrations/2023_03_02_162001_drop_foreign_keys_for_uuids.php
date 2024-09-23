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
            $table->dropColumn(['stage_id', 'updated_by']);
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->dropColumn(['user_id', 'form_id']);
        });

        Schema::table('form_sections', function (Blueprint $table) {
            $table->dropForeign('form_sections_form_id_foreign');
            $table->dropColumn(['form_id']);
        });
    }
};
