<?php

use Illuminate\Database\Seeder;
use App\Models\OfferContact as OfferContactModel;

class OfferContactsTableSeeder extends Seeder
{
    public function run()
    {
        $offerContact = new OfferContactModel();
        $offerContact->id = 1;
        $offerContact->offer_id = 2;
        $offerContact->user_id = 4;
        $offerContact->saveOrFail();
    }
}
