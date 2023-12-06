<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreMissingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->integer('people_knowledge_skills_increased')->nullable()->after('beneficiaries_skills_knowledge_increase_description');
            $table->dropColumn('seedlings_grown');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('v2_project_reports', function (Blueprint $table) {
            $table->dropColumn('people_knowledge_skills_increased');
        });
    }
}
