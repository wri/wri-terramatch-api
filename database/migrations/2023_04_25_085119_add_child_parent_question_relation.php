<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChildParentQuestionRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('form_questions', function (Blueprint $table) {
            $table->foreignUuid('parent_id')->nullable()->after('additional_url')->constrained('form_questions', 'uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('form_questions', function (Blueprint $table) {
            $table->dropColumn([
                'parent_id',
            ]);
        });
    }
}
