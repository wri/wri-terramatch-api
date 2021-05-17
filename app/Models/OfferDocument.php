<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfferDocument extends Model
{
    use NamedEntityTrait;

    public $guarded = [];
    public $timestamps = false;

    use SetAttributeByUploadTrait,
        SoftDeletes;

    public function setDocumentAttribute($document): void
    {
        $this->setAttributeByUpload("document", $document);
    }
}
