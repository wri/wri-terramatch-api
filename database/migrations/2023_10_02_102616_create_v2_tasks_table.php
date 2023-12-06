<?php

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2TasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_tasks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignIdFor(Organisation::class)->nullable();
            $table->foreignIdFor(Project::class)->nullable();
            $table->string('title')->nullable();
            $table->string('status')->nullable();
            $table->string('period_key', 10)->nullable()->index();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_tasks');
    }
}
