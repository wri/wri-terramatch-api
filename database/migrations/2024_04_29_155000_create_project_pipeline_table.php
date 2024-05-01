<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project_pipeline', function (Blueprint $table) {
            $table->id();
            $table->string('Name', 256)->nullable();
            $table->string('SubmittedBy', 256)->nullable();
            $table->string('Description', 500)->nullable();
            $table->string('Program', 256)->nullable();
            $table->string('Cohort', 256)->nullable();
            $table->string('PublishFor', 256)->nullable();
            $table->string('URL', 256)->nullable();
            $table->date('CreatedDate')->nullable();
            $table->date('ModifiedDate')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_pipeline');
    }
};
