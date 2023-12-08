<?php

namespace Database\Seeders;

use App\Models\OfferDocument as OfferDocumentModel;
use Illuminate\Database\Seeder;

class OfferDocumentsTableSeeder extends Seeder
{
    public function run()
    {
        $offerDocumentVersion = new OfferDocumentModel();
        $offerDocumentVersion->id = 1;
        $offerDocumentVersion->offer_id = 1;
        $offerDocumentVersion->name = 'Example Award';
        $offerDocumentVersion->type = 'award';
        $document = DatabaseSeeder::seedRandomObject('file');
        DatabaseSeeder::setRawAttribute($offerDocumentVersion, 'document', $document);
        $offerDocumentVersion->saveOrFail();
    }
}
