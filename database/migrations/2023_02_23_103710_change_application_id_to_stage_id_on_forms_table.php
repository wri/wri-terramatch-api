<?php

use App\Models\V2\Stages\Stage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['application_id']);
            $table->foreignIdFor(Stage::class);
        });
    }
};
