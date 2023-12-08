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
        Schema::create('document_files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('document_fileable_type');
            $table->integer('document_fileable_id');
            $table->string('upload');
            $table->string('title');
            $table->string('collection')->nullable();
            $table->boolean('is_public');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('document_files');
    }
};
