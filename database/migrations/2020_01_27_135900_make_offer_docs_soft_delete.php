<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeOfferDocsSoftDelete extends Migration
{
    public function  up()
    {
        Schema::table("offer_documents", function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
    }
}
