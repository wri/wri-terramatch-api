<?php

use App\Models\V2\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('criteria_site', function (Blueprint $table) {
            $table->dropColumn(['date_created']);
        });
        Schema::table('criteria_site', function (Blueprint $table) {
            $table->foreignIdFor(User::class, 'created_by')->nullable()->after('valid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('criteria_site', function (Blueprint $table) {
            $table->dropColumn(['created_by']);
        });

        Schema::table('criteria_site', function (Blueprint $table) {
            $table->date('date_created')->nullable();
        });
    }
};
