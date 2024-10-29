<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restoration_partners', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->morphs('partnerable', 'partner_morph_index');
            $table->string('collection');
            $table->boolean('hidden')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restoration_partners');
    }
};
