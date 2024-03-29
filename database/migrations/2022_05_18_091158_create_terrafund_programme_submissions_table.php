<?php

use App\Models\Terrafund\TerrafundProgramme;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('terrafund_programme_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TerrafundProgramme::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('terrafund_programme_submissions');
    }
};
