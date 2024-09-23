<?php

use App\Models\Framework;
use App\Models\Organisation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('edit_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->nullable()->unique();
            $table->foreignIdFor(Organisation::class)->nullable();
            $table->foreignIdFor(Framework::class)->nullable();
            $table->string('projectable_type')->nullable();
            $table->integer('projectable_id')->nullable();
            $table->string('editable_type');
            $table->integer('editable_id');

            $table->string('status')->nullable();
            $table->json('content');
            $table->text('comments')->nullable();

            $table->integer('created_by_user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('edit_histories');
    }
};
