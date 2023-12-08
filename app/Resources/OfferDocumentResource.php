<?php

namespace App\Resources;

use App\Models\OfferDocument as OfferDocumentModel;

class OfferDocumentResource
{
    public function __construct(OfferDocumentModel $offerDocument)
    {
        $this->id = $offerDocument->id;
        $this->offer_id = $offerDocument->offer_id;
        $this->name = $offerDocument->name;
        $this->type = $offerDocument->type;
        $this->document = $offerDocument->document;
    }
}
