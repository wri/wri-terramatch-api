<?php

namespace Database\Seeders;

use App\Helpers\I18nHelper;
use App\Models\V2\FundingProgramme;
use Illuminate\Database\Seeder;

class FundingProgrammesLocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fundingProgrammeLocations = FundingProgramme::all();
        $fundingProgrammeLocations->each(function ($fundingProgramme) {
            $fundingProgramme->location_id = I18nHelper::generateI18nItem($fundingProgramme, 'location');
            $fundingProgramme->save();
        });

    }
}
