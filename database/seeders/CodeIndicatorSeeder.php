<?php

namespace Database\Seeder;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CodeIndicatorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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

      foreach ($indicators as $indicator) {
          $indicator['uuid'] = Str::uuid();
          $indicator['uuid_primary'] = $indicator['uuid'];
          DB::table('code_indicator')->insert($indicator);
      }
  }
}