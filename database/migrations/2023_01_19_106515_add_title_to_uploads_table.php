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
        Schema::table('uploads', function (Blueprint $table) {
            $table->string('title')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('uploads', function (Blueprint $table) {
            $table->removeColumn('title');
        });
    }
};
