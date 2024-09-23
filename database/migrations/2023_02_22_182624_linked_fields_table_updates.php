<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('form_sections', function (Blueprint $table) {
            $table->string('title')->nullable()->after('order');
            $table->integer('title_id')->nullable()->after('title');
            $table->string('description')->nullable()->after('title_id');
            $table->integer('description_id')->nullable()->after('description');
        });

        Schema::table('form_questions', function (Blueprint $table) {
            //            $table->integer('linked_field_id')->nullable()->after('form_section_id');
            $table->string('linked_field_key')->nullable()->after('form_section_id');
            $table->integer('label_id')->nullable()->after('label');
            $table->string('description')->nullable()->after('label_id');
            $table->integer('description_id')->nullable()->after('description');
            $table->string('placeholder')->nullable()->after('description_id');
            $table->integer('placeholder_id')->nullable()->after('placeholder');
            $table->string('options_list')->nullable()->after('placeholder_id');
        });

        Schema::table('form_question_options', function (Blueprint $table) {
            $table->integer('label_id')->nullable()->after('label');
        });

        //        Schema::create('linked_fields', function (Blueprint $table) {
        //            $table->id();
        //            $table->uuid();
        //            $table->string('field_name');
        //            $table->string('model');
        //        });

        Schema::create('i18n_items', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->string('type')->default('short');
            $table->string('short_value')->nullable();
            $table->text('long_value')->nullable();
            $table->timestamps();
        });

        Schema::create('i18n_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('i18n_item_id');
            $table->string('language');
            $table->string('short_value')->nullable();
            $table->text('long_value')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::table('form_sections', function (Blueprint $table) {
            $table->dropColumn(['title_id', 'description_id']);
        });

        Schema::table('form_questions', function (Blueprint $table) {
            $table->string('label')->after('form_section_id');
            $table->dropColumn(['linked_field_id', 'label_id', 'description_id', 'placeholder_id']);
        });

        Schema::drop('linked_fields');
        Schema::drop('i18n_items');
        Schema::drop('i18n_translations');
    }
};
