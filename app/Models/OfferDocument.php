<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\SetAttributeDynamicallyTrait;

class OfferDocument extends Model
{
    public $guarded = [];
    public $timestamps = false;

    use SetAttributeDynamicallyTrait;

    public function setDocumentAttribute($document): void
    {
        $this->setAttributeDynamically("document", $document);
    }
}
