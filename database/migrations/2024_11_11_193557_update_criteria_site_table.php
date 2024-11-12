<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('criteria_site', function (Blueprint $table) {
            $table->dropColumn('date_created');
            $table->boolean('is_active')->default(false)->after('extra_info');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('criteria_site', function (Blueprint $table) {
            $table->timestamp('date_created')->nullable();
            $table->dropColumn('is_active');
        });
    }
};
