<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    public function up()
    {
        Schema::create("offers", function(Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("organisation_id")->unsigned();
            $table->string("name");
            $table->longText("description");
            $table->json("land_types");
            $table->json("land_ownerships");
            $table->string("land_size");
            $table->string("land_continent");
            $table->string("land_country")->nullable();
            $table->json("restoration_methods");
            $table->json("restoration_goals");
            $table->json("funding_sources");
            $table->string("funding_amount");
            $table->string("funding_amount_currency");
            $table->float("price_per_tree")->nullable();
            $table->string("price_per_tree_currency")->nullable();
            $table->boolean("long_term_engagement")->nullable();
            $table->string("reporting_frequency");
            $table->string("reporting_level");
            $table->json("sustainable_development_goals");
            $table->timestamps();
            $table->foreign("organisation_id")->references("id")->on("organisations");
        });
        Schema::create("offer_contacts", function(Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("offer_id")->unsigned();
            $table->bigInteger("user_id")->unsigned()->nullable();
            $table->bigInteger("team_member_id")->unsigned()->nullable();
            $table->foreign("offer_id")->references("id")->on("offers");
            $table->foreign("user_id")->references("id")->on("users");
            $table->foreign("team_member_id")->references("id")->on("team_members");
        });
    }

    public function down()
    {
    }
}
