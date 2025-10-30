<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('srp_reports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('status');
            $table->string('title');
            $table->text('other_restoration_partners_description')->nullable();
            $table->unsignedInteger('total_unique_restoration_partners')->nullable();
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
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('annual_socio_economic_restoration_reports');
    }
};
