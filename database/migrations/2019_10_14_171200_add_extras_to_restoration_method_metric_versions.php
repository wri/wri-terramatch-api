<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddExtrasToRestorationMethodMetricVersions extends Migration
{
    public function up()
    {
        Schema::table("restoration_method_metric_versions", function (Blueprint $table) {
            $table->string("restoration_method");
        });
    }

    public function down()
    {
    }
}
