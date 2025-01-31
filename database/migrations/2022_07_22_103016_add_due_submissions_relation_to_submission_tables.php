<?php

use App\Models\Terrafund\TerrafundDueSubmission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('terrafund_site_submissions', function (Blueprint $table) {
            $table->foreignIdFor(TerrafundDueSubmission::class)
                ->nullable()
                ->after('terrafund_site_id');
        });

        Schema::table('terrafund_nursery_submissions', function (Blueprint $table) {
            $table->foreignIdFor(TerrafundDueSubmission::class)
                ->nullable()
                ->after('terrafund_nursery_id');
        });
    }
};
