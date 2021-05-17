<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            UPDATE `notifications` SET `action` = LOWER(`action`);
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("
            UPDATE `notifications` SET `action` = UPPER(`action`);
        ");
    }
}
