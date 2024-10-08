<?php

use App\Models\V2\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('project_pipelines', function (Blueprint $table) {
            $table->id();
            $table->string('name', 256)->nullable();
            $table->foreignIdFor(User::class, 'submitted_by')->nullable();
            $table->string('description', 500)->nullable();
            $table->string('program', 256)->nullable();
            $table->string('cohort', 256)->nullable();
            $table->string('publish_for', 256)->nullable();
            $table->string('url', 256)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_pipelines');
    }
};
