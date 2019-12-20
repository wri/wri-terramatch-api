<?php

use Illuminate\Database\Seeder;
use App\Models\OfferDocument as OfferDocumentModel;
use Illuminate\Support\Facades\Config;

class OfferDocumentsTableSeeder extends Seeder
{
    public function run()
    {
        $offerDocumentVersion = new OfferDocumentModel();
        $offerDocumentVersion->id = 1;
        $offerDocumentVersion->offer_id = 1;
        $offerDocumentVersion->name = "Example Award";
        $offerDocumentVersion->type = "award";
        $document = "http://127.0.0.1:9000" . "/" . Config::get("app.s3.bucket") . "/D9kyqFqvD4U9Mwmm3haqdqN7a2njo7ve5d9719423928c.pdf";
        DatabaseSeeder::setRawAttribute($offerDocumentVersion, "document", $document);
        $offerDocumentVersion->saveOrFail();
    }
}
