<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('terrafund_due_submissions', function (Blueprint $table) {
            $table->text('unable_report_reason')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('terrafund_due_submissions', function (Blueprint $table) {
            $table->dropColumn('unable_report_reason');
        });
    }
};
