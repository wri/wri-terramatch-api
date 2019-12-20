<?php

use Illuminate\Database\Seeder;
use App\Models\Interest as InterestModel;

class InterestsTableSeeder extends Seeder
{
    public function run()
    {
        $interest = new InterestModel();
        $interest->id = 1;
        $interest->organisation_id = 2;
        $interest->initiator = "offer";
        $interest->offer_id = 2;
        $interest->pitch_id = 1;
        $interest->matched = false;
        $interest->saveOrFail();

        $interest = new InterestModel();
        $interest->id = 2;
        $interest->organisation_id = 1;
        $interest->initiator = "pitch";
        $interest->offer_id = 2;
        $interest->pitch_id = 1;
        $interest->matched = false;
        $interest->saveOrFail();
    }
}
