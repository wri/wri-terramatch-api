<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('edit_histories', function (Blueprint $table) {
            $table->string('project_name')
                ->after('projectable_id')
                ->nullable()
                ->index();
        });
    }

    public function down()
    {
        Schema::table('edit_histories', function (Blueprint $table) {
            $table->dropColumn('project_name');
        });
    }
};
