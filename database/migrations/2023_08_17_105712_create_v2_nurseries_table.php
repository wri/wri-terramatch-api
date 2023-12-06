<?php

use App\Models\V2\Projects\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2NurseriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_nurseries', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('framework_key', 20)->nullable()->index();
            $table->foreignIdFor(Project::class)->nullable();
            $table->string('status')->nullable();
            $table->text('name')->nullable();
            $table->text('planting_contribution')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('type')->nullable();
            $table->unsignedInteger('seedling_grown')->nullable();

            $table->unsignedInteger('old_id')->nullable();
            $table->string('old_model')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('v2_nurseries');
    }
}
