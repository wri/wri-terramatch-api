<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUpdateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_update_requests', function (Blueprint $table) {
            $table->renameColumn('comments', 'feedback');
            $table->text('feedback_fields')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_update_requests', function (Blueprint $table) {
            $table->renameColumn('feedback', 'comments');
            $table->dropColumn('feedback_fields', 'feedback');
        });
    }
}
