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
        Schema::table('organisation_versions', function (Blueprint $table) {
            $table->decimal('revenues_19', 15, 2)->nullable()->change();
            $table->decimal('revenues_20', 15, 2)->nullable()->change();
            $table->decimal('revenues_21', 15, 2)->nullable()->change();
        });
    }
};
