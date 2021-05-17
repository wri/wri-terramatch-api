<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOtherType extends Migration
{
    public function up()
    {
        Schema::table('carbon_certification_versions', function (Blueprint $table) {
            $table->dropColumn("other_type");
        });
    }

    public function down()
    {
    }
}
