<?php

namespace App\Models;

use App\Models\Interfaces\Version;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Models\Traits\IsVersion;
use Illuminate\Database\Eloquent\SoftDeletes;

class PitchDocumentVersion extends Model implements Version
{
    use NamedEntityTrait, SoftDeletes, IsVersion, SetAttributeByUploadTrait;

    protected $parentClass = "App\\Models\\PitchDocument";

    public $guarded = [];
    public $dates = [
        "approved_rejected_at"
    ];

    public function setDocumentAttribute($document): void
    {
        $this->setAttributeByUpload("document", $document);
    }
}
