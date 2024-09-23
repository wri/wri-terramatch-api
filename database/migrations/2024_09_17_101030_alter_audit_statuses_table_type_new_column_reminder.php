<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE audit_statuses MODIFY COLUMN type enum('change-request', 'status', 'submission', 'comment', 'change-request-updated', 'reminder-sent')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE audit_statuses MODIFY COLUMN type enum('change-request', 'status', 'submission', 'comment')");
    }
};
