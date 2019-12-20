<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateOfferDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create("offer_documents", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("offer_id")->unsigned();
            $table->string("name");
            $table->string("type");
            $table->string("document");
            $table->foreign("offer_id")->references("id")->on("offers");
        });
    }

    public function down()
    {
    }
}
