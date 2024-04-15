<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateCodeIndicator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::dropIfExists('code_indicator');
      Schema::create('code_indicator', function (Blueprint $table) {
        $table->id();
        $table->uuid('uuid')->unique();
        $table->string('uuid_primary');
        $table->string('name');
        $table->string('unit');
        $table->string('description');
        $table->integer('is_active');
        $table->softDeletes();
        $table->timestamps();
      });
      $indicators = [
        [
            'name' => 'Tree cover (TTC)',
            'unit' => 'Percent',
            'description' => 'Percent tree cover of each site polygon',
            'is_active' => 1
        ],
        [
            'name' => 'Tree cover loss',
            'unit' => 'Ha',
            'description' => 'Tree cover loss in hectares',
            'is_active' => 1
        ],
        [
            'name' => 'Tree cover loss from fires',
            'unit' => 'Ha',
            'description' => 'Tree cover loss from fires in hectares',
            'is_active' => 1
        ],
        [
            'name' => 'Hectares under restoration by WWF ecoregion',
            'unit' => 'Ha',
            'description' => 'Area value for each ecoregion type',
            'is_active' => 1
        ],
        [
            'name' => 'Hectares under restoration by intervention type',
            'unit' => 'Ha',
            'description' => 'Area value for each intervention type',
            'is_active' => 1
        ],
        [
            'name' => 'Tree count',
            'unit' => 'Count',
            'description' => 'Tree count number and confidence value',
            'is_active' => 1
        ],
      ];
      $now = now(); 
      foreach ($indicators as $indicator) {
          $indicator['uuid'] = Str::uuid();
          $indicator['uuid_primary'] = $indicator['uuid'];
          $indicator['created_at'] = $now;
          $indicator['updated_at'] = $now;
          DB::table('code_indicator')->insert($indicator);
      }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::dropIfExists('code_indicator');
    }
}
