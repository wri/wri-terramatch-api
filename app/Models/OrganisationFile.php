<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Model;

class OrganisationFile extends Model
{
    use NamedEntityTrait;
    use SetAttributeByUploadTrait;

    public $fillable = [
        'organisation_id',
        'upload',
        'type',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function setUploadAttribute($upload): void
    {
        $this->setAttributeByUpload('upload', $upload);
    }
}
