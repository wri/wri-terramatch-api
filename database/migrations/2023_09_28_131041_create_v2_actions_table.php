<?php

use App\Models\V2\Organisation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateV2ActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('v2_actions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->string('subtype')->nullable();
            $table->string('key')->nullable();
            $table->morphs('targetable');
            $table->foreignIdFor(Organisation::class);
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
        Schema::dropIfExists('actions');
    }
}
