<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    public function up()
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn("email_verified_at");
            $table->dateTimeTz('email_address_verified_at')->nullable();
            $table->dropColumn("isAdmin");
            $table->enum("role", ["user", "admin"])->default("user");
            $table->dateTimeTz('last_logged_in_at')->nullable();
        });
        DB::statement("
            ALTER TABLE users 
            CHANGE COLUMN `surname` `last_name` varchar(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL,
            CHANGE COLUMN `email` `email_address` varchar(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL;
        ");
    }

    public function down()
    {
    }
}
