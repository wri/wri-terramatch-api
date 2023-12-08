<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeFieldsNullableOnFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->text('subtitle')->nullable()->change();
            $table->text('documentation')->nullable()->change();
            $table->text('duration')->nullable()->change();
        });
    }
}
