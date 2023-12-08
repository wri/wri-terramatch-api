<?php

use App\Models\V2\User;
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
            $table->foreignIdFor(User::class, 'updated_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('forms', function (Blueprint $table) {
            $table->dropColumn(['updated_by']);
        });
    }
};
