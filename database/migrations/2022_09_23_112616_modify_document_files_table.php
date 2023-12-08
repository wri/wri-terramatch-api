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
        Schema::table('document_files', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
            $table->boolean('is_public')->default(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
};
