<?php

namespace App\Models;

use App\Models\Contracts\NamedEntity;
use App\Models\Contracts\Version;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\SetAttributeDynamicallyTrait;

class PitchDocumentVersion extends Model implements Version, NamedEntity
{
    use NamedEntityTrait,
        SetAttributeDynamicallyTrait;

    public $guarded = [];
    public $timestamps = false;
    public $dates = [
        "approved_rejected_at"
    ];

    public function setDocumentAttribute($document): void
    {
        $this->setAttributeDynamically("document", $document);
    }
}
