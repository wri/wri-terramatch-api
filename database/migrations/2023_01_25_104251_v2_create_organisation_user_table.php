<?php

use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('organisation_user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Organisation::class);
            $table->string('status', 30)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('organisation_user');
    }
};
