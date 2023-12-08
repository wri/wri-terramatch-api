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
            $table->dropConstrainedForeignId('form_id');
            $table->foreignId('form_section_id')->after('id')->constrained()->cascadeOnDelete();
            $table->uuid('uuid')->after('id')->unique()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('form_questions', function (Blueprint $table) {
            $table->dropForeign(['form_section_id']);
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->dropColumn('uuid');
        });
    }
};
