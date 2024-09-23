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
            $table->unsignedInteger('ft_men')->after('ft_women');
            $table->unsignedInteger('pt_men')->after('pt_women');
            $table->unsignedInteger('seasonal_men')->after('seasonal_women');
            $table->unsignedInteger('volunteer_men')->after('volunteer_women');

            $table->unsignedInteger('ft_total')->after('ft_smallholder_farmers');
            $table->unsignedInteger('pt_total')->after('pt_smallholder_farmers');
            $table->unsignedInteger('seasonal_total')->after('seasonal_smallholder_farmers');
            $table->unsignedInteger('volunteer_total')->after('volunteer_smallholder_farmers');

            $table->dropColumn(['ft_indigenous_people', 'pt_indigenous_people', 'seasonal_indigenous_people', 'volunteer_indigenous_people']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('terrafund_programme_submissions', function (Blueprint $table) {
            $table->unsignedInteger('ft_indigenous_people')->after('ft_youth');
            $table->unsignedInteger('pt_indigenous_people')->after('pt_youth');
            $table->unsignedInteger('seasonal_indigenous_people')->after('seasonal_youth');
            $table->unsignedInteger('volunteer_indigenous_people')->after('volunteer_youth');

            $table->dropColumn([
                'ft_total',
                'pt_total',
                'seasonal_total',
                'volunteer_total',
                'ft_men',
                'pt_men',
                'seasonal_men',
                'volunteer_men',
            ]);
        });
    }
};
