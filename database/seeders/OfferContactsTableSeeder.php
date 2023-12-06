<?php

namespace Database\Seeders;

use App\Models\OfferContact as OfferContactModel;
use Illuminate\Database\Seeder;

class OfferContactsTableSeeder extends Seeder
{
    public function run()
    {
        $offerContact = new OfferContactModel();
        $offerContact->id = 1;
        $offerContact->offer_id = 2;
        $offerContact->user_id = 4;
        $offerContact->saveOrFail();

        $offerContact = new OfferContactModel();
        $offerContact->id = 2;
        $offerContact->offer_id = 4;
        $offerContact->user_id = 4;
        $offerContact->saveOrFail();

        $offerContact = new OfferContactModel();
        $offerContact->id = 3;
        $offerContact->offer_id = 5;
        $offerContact->user_id = 4;
        $offerContact->saveOrFail();

        $offerContact = new OfferContactModel();
        $offerContact->id = 4;
        $offerContact->offer_id = 5;
        $offerContact->team_member_id = 1;
        $offerContact->saveOrFail();
    }
}
