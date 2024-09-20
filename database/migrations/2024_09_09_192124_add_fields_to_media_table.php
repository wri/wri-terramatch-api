<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->boolean('is_cover')->default(false)->after('is_public');
            $table->string('photographer', 100)->nullable()->after('order_column');
            $table->string('description', 500)->nullable()->after('photographer');
            $table->string('tag')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['is_cover', 'photographer', 'description', 'tag']);
        });
    }
}
