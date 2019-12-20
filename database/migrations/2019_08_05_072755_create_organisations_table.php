<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateOrganisationsTable.
 */
class CreateOrganisationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('organisations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('organisation_name', 50);
            $table->string('first_line_address', 50);
            $table->string('second_line_address', 50);
            $table->string('city', 50);
            $table->string('zip_code', 9);
            $table->string('contact_number', 12);
            $table->string('point_of_contact', 50);
            $table->string('country', 50);
            $table->string('website')->nullable();
            $table->longText('description');
            $table->string('organisation_type');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
	}
}
