<?php

use App\Models\V2\Workdays\Workday;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('workday_demographics')) {
            Schema::create('workday_demographics', function (Blueprint $table) {
                $table->id();
                $table->foreignIdFor(Workday::class);
                $table->string('type');
                $table->string('subtype')->nullable();
                $table->string('name')->nullable();
                $table->integer('amount');

                $table->softDeletes();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workday_demographics');
    }
};
