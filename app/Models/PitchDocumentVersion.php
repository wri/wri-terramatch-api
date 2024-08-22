<?php

namespace App\Models;

use App\Models\Interfaces\Version;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PitchDocumentVersion extends Model implements Version
{
    use NamedEntityTrait;
    use SoftDeletes;
    use IsVersion;
    use SetAttributeByUploadTrait;

    protected $parentClass = \App\Models\PitchDocument::class;

    public $fillable = [
        'pitch_document_id',
        'name',
        'type',
        'document',
        'rejected_reason',
        'approved_rejected_by',
        'approved_rejected_at',
        'rejected_reason_body',
        'status',
    ];

    protected $casts = [
        'approved_rejected_at' => 'datetime',
    ];

    public function pitchDocument()
    {
        return $this->belongsTo(PitchDocument::class);
    }

    public function approvedRejectedBy()
    {
        return $this->belongsTo(User::class, 'approved_rejected_by');
    }

    public function setDocumentAttribute($document): void
    {
        $this->setAttributeByUpload('document', $document);
    }
}
