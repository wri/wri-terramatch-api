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
        Schema::table('terrafund_programmes', function (Blueprint $table) {
            $table->dropColumn('programme_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('terrafund_programmes', function (Blueprint $table) {
            $table->text('programme_type')->nullable();
        });
    }
};
