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
        Schema::table('form_questions', function (Blueprint $table) {
            $table->json('additional_props')->nullable()->after('order');
        });
    }
};
