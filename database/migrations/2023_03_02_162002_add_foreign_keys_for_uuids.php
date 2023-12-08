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
        Schema::table('forms', function (Blueprint $table) {
            $table->foreignUuid('stage_id')->nullable();
            $table->foreignUuid('updated_by')->nullable();
        });

        Schema::table('form_submissions', function (Blueprint $table) {
            $table->foreignUuid('user_id')->nullable();
            $table->foreignUuid('form_id')->nullable();
        });

        Schema::table('form_sections', function (Blueprint $table) {
            $table->foreignUuid('form_id')->nullable();
        });
    }
};
