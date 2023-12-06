<?php

use App\Models\Framework;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint ;
use Illuminate\Support\Str;

class AddSlugToFrameworksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('frameworks', function (Blueprint $table) {
            $table->string('slug', 20)
                ->nullable()
                ->index()
                ->after('name');
        });

        $frameworks = Framework::all();
        foreach ($frameworks as $framework) {
            $framework->slug = Str::slug($framework->name);

            $framework->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('frameworks', function (Blueprint $table) {
            Schema::dropColumns('slug');
        });
    }
}
