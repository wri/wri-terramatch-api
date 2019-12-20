<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreatePitchesTables extends Migration
{
    public function up()
    {
        // pitches
        Schema::create("pitches", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("organisation_id")->unsigned();
            $table->foreign("organisation_id")->references("id")->on("organisations");
        });
        Schema::create("pitch_versions", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("pitch_id")->unsigned();
            $table->enum("status",["pending", "approved", "rejected", "archived"])->default("pending");
            $table->longText("rejected_reason")->nullable();
            $table->bigInteger("approved_rejected_by")->unsigned()->nullable();
            $table->timestamp("approved_rejected_at")->nullable();
            $table->string("name");
            $table->longText("description");
            $table->json("land_types");
            $table->json("land_ownerships");
            $table->float("land_size");
            $table->string("land_continent");
            $table->string("land_country");
            $table->json("land_geojson");
            $table->json("restoration_methods");
            $table->json("restoration_goals");
            $table->json("funding_sources");
            $table->integer("funding_amount");
            $table->json("revenue_drivers");
            $table->float("estimated_timespan_in_years");
            $table->boolean("long_term_engagement")->nullable();;
            $table->string("reporting_frequency");
            $table->string("reporting_level");
            $table->json("sustainable_development_goals");
            $table->string("cover_photo")->nullable();
            $table->string("avatar")->nullable();
            $table->string("video")->nullable();
            $table->timestamps();
            $table->foreign("pitch_id")->references("id")->on("pitches");
            $table->foreign("approved_rejected_by")->references("id")->on("users");
        });
        // carbon_certifications
        Schema::create("carbon_certifications", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("pitch_id")->unsigned();
            $table->foreign("pitch_id")->references("id")->on("pitches");
        });
        Schema::create("carbon_certification_versions", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("carbon_certification_id")->unsigned();
            $table->enum("status",["pending", "approved", "rejected", "archived"])->default("pending");
            $table->longText("rejected_reason")->nullable();
            $table->bigInteger("approved_rejected_by")->unsigned()->nullable();
            $table->timestamp("approved_rejected_at")->nullable();
            $table->string("type");
            $table->longText("link");
            $table->foreign("carbon_certification_id")->references("id")->on("carbon_certifications");
            $table->foreign("approved_rejected_by")->references("id")->on("users");
        });
        // tree_species
        Schema::create("tree_species", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("pitch_id")->unsigned();
            $table->foreign("pitch_id")->references("id")->on("pitches");
        });
        Schema::create("tree_species_versions", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("tree_species_id")->unsigned();
            $table->enum("status",["pending", "approved", "rejected", "archived"])->default("pending");
            $table->longText("rejected_reason")->nullable();
            $table->bigInteger("approved_rejected_by")->unsigned()->nullable();
            $table->timestamp("approved_rejected_at")->nullable();
            $table->string("name");
            $table->string("is_native");
            $table->bigInteger("count");
            $table->float("price_to_obtain");
            $table->float("price_to_plant");
            $table->float("price_to_maintain");
            $table->integer("survival_rate")->nullable();
            $table->boolean("produces_food")->nullable();
            $table->boolean("produces_firewood")->nullable();
            $table->boolean("produces_timber")->nullable();
            $table->string("owner");
            $table->foreign("tree_species_id")->references("id")->on("tree_species");
            $table->foreign("approved_rejected_by")->references("id")->on("users");
        });
        // pitch_documents
        Schema::create("pitch_documents", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("pitch_id")->unsigned();
            $table->foreign("pitch_id")->references("id")->on("pitches");
        });
        Schema::create("pitch_document_versions", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("pitch_document_id")->unsigned();
            $table->enum("status",["pending", "approved", "rejected", "archived"])->default("pending");
            $table->longText("rejected_reason")->nullable();
            $table->bigInteger("approved_rejected_by")->unsigned()->nullable();
            $table->timestamp("approved_rejected_at")->nullable();
            $table->string("name");
            $table->string("type");
            $table->string("document");
            $table->foreign("pitch_document_id")->references("id")->on("pitch_documents");
            $table->foreign("approved_rejected_by")->references("id")->on("users");
        });
        // pitch_contacts
        Schema::create("pitch_contacts", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("pitch_id")->unsigned();
            $table->bigInteger("user_id")->unsigned()->nullable();
            $table->bigInteger("team_member_id")->unsigned()->nullable();
            $table->foreign("pitch_id")->references("id")->on("pitches");
            $table->foreign("user_id")->references("id")->on("users");
            $table->foreign("team_member_id")->references("id")->on("team_members");
        });
        // restoration_method_metrics
        Schema::create("restoration_method_metrics", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("pitch_id")->unsigned();
            $table->foreign("pitch_id")->references("id")->on("pitches");
        });
        Schema::create("restoration_method_metric_versions", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->bigInteger("restoration_method_metric_id")->unsigned();
            $table->enum("status",["pending", "approved", "rejected", "archived"])->default("pending");
            $table->longText("rejected_reason")->nullable();
            $table->bigInteger("approved_rejected_by")->unsigned()->nullable();
            $table->timestamp("approved_rejected_at")->nullable();
            $table->float("experience_in_years");
            $table->float("land_size");
            $table->float("price_per_hectare");
            $table->float("biomass_per_hectare")->nullable();
            $table->float("carbon_impact")->nullable();
            $table->json("species_impacted");
            $table->foreign("restoration_method_metric_id", "rmmv_rmm_foreign")->references("id")->on("restoration_method_metrics");
            $table->foreign("approved_rejected_by")->references("id")->on("users");
        });
    }

    public function down()
    {
        Schema::drop("pitches");
        Schema::drop("pitch_versions");
        Schema::drop("carbon_certifications");
        Schema::drop("carbon_certification_versions");
        Schema::drop("tree_species");
        Schema::drop("tree_species_versions");
        Schema::drop("pitch_documents");
        Schema::drop("pitch_document_versions");
        Schema::drop("pitch_contacts");
        Schema::drop("pitch_contact_versions");
        Schema::drop("restoration_method_metrics");
        Schema::drop("restoration_method_metric_versions");
    }
}
