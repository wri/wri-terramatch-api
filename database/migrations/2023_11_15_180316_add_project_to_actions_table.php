<?php

use App\Models\V2\Projects\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectToActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_actions', function (Blueprint $table) {
            $table->foreignIdFor(Project::class, 'project_id')->index()->nullable()->after('organisation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_actions', function (Blueprint $table) {
            $table->dropColumn('project_id');
        });
    }
}
