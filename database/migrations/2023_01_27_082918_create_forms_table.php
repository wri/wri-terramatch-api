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
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique()->index();
            $table->foreignId('application_id'); // 2023-02-23 - this has been updated, so that migrations can run locally, but won't break anything
            $table->unsignedBigInteger('version')->nullable();
            $table->text('title');
            $table->text('subtitle');
            $table->text('description');
            $table->text('documentation');
            $table->text('submission_message')->nullable();
            $table->string('document')->nullable();
            $table->text('duration');
            $table->boolean('published')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('forms');
    }
};
