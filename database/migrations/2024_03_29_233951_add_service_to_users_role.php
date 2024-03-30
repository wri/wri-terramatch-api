<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // $table->enum(...)->change() is not supported
        // https://github.com/laravel/framework/issues/35096
        DB::statement("ALTER TABLE users MODIFY COLUMN role enum('user', 'admin', 'terrafund_admin', 'service')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role enum('user', 'admin', 'terrafund_admin')");
    }
};
