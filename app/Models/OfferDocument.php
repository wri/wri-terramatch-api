<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfferDocument extends Model
{
    use NamedEntityTrait;

    use SetAttributeByUploadTrait;

    use SoftDeletes;

    public $fillable = [
        'offer_id',
        'name',
        'type',
        'document',
    ];

    public $timestamps = false;

    public function setDocumentAttribute($document): void
    {
        $this->setAttributeByUpload('document', $document);
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class);
    }
}
