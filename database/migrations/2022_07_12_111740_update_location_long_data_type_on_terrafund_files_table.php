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
        Schema::table('terrafund_files', function (Blueprint $table) {
            $table->decimal('location_long', 11, 8)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('terrafund_files', function (Blueprint $table) {
            $table->decimal('location_long', 10, 8)->nullable()->change();
        });
    }
};
