<?php

use App\Models\V2\FundingProgramme;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSavedExportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saved_exports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->text('name')->nullable();
            $table->foreignIdFor(FundingProgramme::class);
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
        Schema::dropIfExists('saved_exports');
    }
}
