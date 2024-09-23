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
        Schema::table('i18n_items', function (Blueprint $table) {
            $table->string('hash')->nullable()->after('long_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('i18n_items', function (Blueprint $table) {
            $table->dropColumn('hash');
        });
    }
};
