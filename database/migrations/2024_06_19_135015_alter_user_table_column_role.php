<?php

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role enum('user', 'admin', 'terrafund_admin', 'service', 'funder', 'government', 'project-developer')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role enum('user', 'admin', 'terrafund_admin', 'service')");
    }
};
