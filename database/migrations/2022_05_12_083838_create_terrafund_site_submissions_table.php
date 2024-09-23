<?php

use App\Models\Terrafund\TerrafundSite;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('terrafund_site_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TerrafundSite::class);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('terrafund_site_submissions');
    }
};
