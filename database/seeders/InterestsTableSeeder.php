<?php

namespace Database\Seeders;

use App\Models\Interest as InterestModel;
use Illuminate\Database\Seeder;

class InterestsTableSeeder extends Seeder
{
    public function run()
    {
        $interest = new InterestModel();
        $interest->id = 1;
        $interest->organisation_id = 2;
        $interest->initiator = 'offer';
        $interest->offer_id = 2;
        $interest->pitch_id = 1;
        $interest->has_matched = false;
        $interest->saveOrFail();

        $interest = new InterestModel();
        $interest->id = 2;
        $interest->organisation_id = 1;
        $interest->initiator = 'pitch';
        $interest->offer_id = 2;
        $interest->pitch_id = 1;
        $interest->has_matched = false;
        $interest->saveOrFail();

        $interest = new InterestModel();
        $interest->id = 3;
        $interest->organisation_id = 1;
        $interest->initiator = 'pitch';
        $interest->offer_id = 4;
        $interest->pitch_id = 2;
        $interest->has_matched = true;
        $interest->saveOrFail();

        $interest = new InterestModel();
        $interest->id = 4;
        $interest->organisation_id = 2;
        $interest->initiator = 'offer';
        $interest->offer_id = 4;
        $interest->pitch_id = 2;
        $interest->has_matched = true;
        $interest->saveOrFail();

        $interest = new InterestModel();
        $interest->id = 5;
        $interest->organisation_id = 1;
        $interest->initiator = 'pitch';
        $interest->offer_id = 5;
        $interest->pitch_id = 2;
        $interest->has_matched = true;
        $interest->saveOrFail();

        $interest = new InterestModel();
        $interest->id = 6;
        $interest->organisation_id = 2;
        $interest->initiator = 'offer';
        $interest->offer_id = 5;
        $interest->pitch_id = 2;
        $interest->has_matched = true;
        $interest->saveOrFail();
    }
}
