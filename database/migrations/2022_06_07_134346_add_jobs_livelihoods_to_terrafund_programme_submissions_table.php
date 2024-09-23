<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('terrafund_programme_submissions', function (Blueprint $table) {
            $table->unsignedInteger('ft_women');
            $table->unsignedInteger('ft_youth');
            $table->unsignedInteger('ft_indigenous_people');
            $table->unsignedInteger('ft_smallholder_farmers');
            $table->unsignedInteger('pt_women');
            $table->unsignedInteger('pt_youth');
            $table->unsignedInteger('pt_indigenous_people');
            $table->unsignedInteger('pt_smallholder_farmers');
            $table->unsignedInteger('seasonal_women');
            $table->unsignedInteger('seasonal_youth');
            $table->unsignedInteger('seasonal_indigenous_people');
            $table->unsignedInteger('seasonal_smallholder_farmers');
            $table->unsignedInteger('volunteer_women');
            $table->unsignedInteger('volunteer_youth');
            $table->unsignedInteger('volunteer_indigenous_people');
            $table->unsignedInteger('volunteer_smallholder_farmers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('terrafund_programme_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'ft_women',
                'ft_youth',
                'ft_indigenous_people',
                'ft_smallholder_farmers',
                'pt_women',
                'pt_youth',
                'pt_indigenous_people',
                'pt_smallholder_farmers',
                'seasonal_women',
                'seasonal_youth',
                'seasonal_indigenous_people',
                'seasonal_smallholder_farmers',
                'volunteer_women',
                'volunteer_youth',
                'volunteer_indigenous_people',
                'volunteer_smallholder_farmers',
            ]);
        });
    }
};
