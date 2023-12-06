<?php

use App\Models\DueSubmission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('site_submissions', function (Blueprint $table) {
            $table->foreignIdFor(DueSubmission::class)
                ->nullable()
                ->after('site_id');
        });
    }
};
