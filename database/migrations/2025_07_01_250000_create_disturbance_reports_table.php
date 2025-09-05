<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('disturbance_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('status');
            $table->string('title');
            $table->string('disturbance_type')->nullable();
            $table->string('disturbance_subtype')->nullable();
            $table->string('extent')->nullable();
            $table->integer('people_affected')->nullable();
            $table->date('date_of_disturbance')->nullable();
            $table->decimal('monetary_damage', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->text('action_description')->nullable();
            $table->string('update_request_status')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();
            $table->boolean('nothing_to_report')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('framework_key')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->integer('completion')->nullable();
            $table->text('feedback')->nullable();
            $table->json('feedback_fields')->nullable();
            $table->json('answers')->nullable();
            $table->date('date_of_incident')->nullable();
            $table->string('intensity')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('disturbance_reports');
    }
};
