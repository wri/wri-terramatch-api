<?php

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2UpdateRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_update_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(Organisation::class, 'organisation_id')->index()->nullable();
            $table->foreignIdFor(Project::class, 'project_id')->index()->nullable();
            $table->foreignIdFor(User::class, 'created_by_id')->index()->nullable();
            $table->string('framework_key')->index()->nullable();
            $table->string('updaterequestable_type')->index('updaterequestable_type_index');
            $table->unsignedInteger('updaterequestable_id')->index('updaterequestable_id_index');
            $table->string('status')->nullable();
            $table->longText('content')->nullable();
            $table->text('comments')->nullable();

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
        Schema::dropIfExists('v2_update_requests');
    }
}
