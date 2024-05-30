<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('status');
        Schema::create('status', function (Blueprint $table) {
            $table->id();
            $table->string('entity')->nullable();
            $table->string('entity_uuid')->nullable();
            $table->string('status')->nullable();
            $table->string('comment')->nullable();
            $table->string('attachment_url')->nullable();
            $table->date('date_created')->nullable();
            $table->string('created_by')->nullable();
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
        Schema::dropIfExists('status');
    }
};
