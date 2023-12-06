<?php

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2NurseryReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_nursery_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('framework_key', 20)->nullable()->index();
            $table->foreignIdFor(Nursery::class)->nullable();
            $table->foreignIdFor(User::class, 'created_by')->nullable();
            $table->foreignIdFor(User::class, 'approved_by')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->text('title')->nullable();
            $table->text('interesting_facts')->nullable();
            $table->text('site_prep')->nullable();
            $table->unsignedInteger('seedlings_young_trees')->nullable();
            $table->string('shared_drive_link')->nullable();

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
        Schema::dropIfExists('v2_project_reports');
    }
}
