<?php

namespace Database\Seeders;

use App\Models\Matched as MatchModel;
use Illuminate\Database\Seeder;

class MatchesTableSeeder extends Seeder
{
    public function run()
    {
        $match = new MatchModel();
        $match->id = 1;
        $match->primary_interest_id = 1;
        $match->secondary_interest_id = 2;
        $match->saveOrFail();

        $match = new MatchModel();
        $match->id = 2;
        $match->primary_interest_id = 3;
        $match->secondary_interest_id = 4;
        $match->saveOrFail();

        $match = new MatchModel();
        $match->id = 3;
        $match->primary_interest_id = 5;
        $match->secondary_interest_id = 6;
        $match->saveOrFail();
    }
}
