<?php

use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2ProjectUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_project_users', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Project::class, 'project_id')->index()->nullable();
            $table->foreignIdFor(User::class, 'user_id')->index()->nullable();
            $table->string('status')->nullable();
            $table->boolean('is_monitoring')->nullable()->default(0);
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
        Schema::dropIfExists('v2_project_users');
    }
}
