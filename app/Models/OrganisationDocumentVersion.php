<?php

namespace App\Models;

use App\Models\Interfaces\Version;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganisationDocumentVersion extends Model implements Version
{
    use NamedEntityTrait;
    use SoftDeletes;
    use IsVersion;
    use SetAttributeByUploadTrait;

    protected $parentClass = \App\Models\OrganisationDocument::class;

    public $fillable = [
        'organisation_document_id',
        'name',
        'type',
        'document',
        'status',
        'approved_rejected_by',
        'approved_rejected_at',
        'rejected_reason',
        'rejected_reason_body',
    ];

    protected $casts = [
        'approved_rejected_at' => 'datetime',
    ];

    public function setDocumentAttribute($document): void
    {
        $this->setAttributeByUpload('document', $document);
    }

    public function organisationDocument()
    {
        return $this->belongsTo(OrganisationDocument::class);
    }
}
