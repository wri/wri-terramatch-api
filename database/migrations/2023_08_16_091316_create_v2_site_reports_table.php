<?php

use App\Models\V2\Sites\Site;
use App\Models\V2\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint ;

class CreateV2SiteReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_site_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('framework_key', 20)->nullable()->index();
            $table->foreignIdFor(Site::class)->nullable();
            $table->foreignIdFor(User::class, 'approved_by')->nullable();
            $table->string('status')->nullable();
            $table->string('created_by')->nullable();
            $table->string('title')->nullable();
            $table->text('technical_narrative')->nullable();
            $table->text('public_narrative')->nullable();
            $table->unsignedInteger('workdays_paid')->nullable();
            $table->unsignedInteger('workdays_volunteer')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->date('approved_at')->nullable();
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
        Schema::dropIfExists('v2_site_reports');
    }
}
