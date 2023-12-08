<?php

namespace App\Models;

use App\Models\Traits\HasVersions;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganisationDocument extends Model
{
    use SoftDeletes;
    use HasVersions;
    use NamedEntityTrait;

    protected $versionClass = \App\Models\OrganisationDocumentVersion::class;

    public $fillable = [
        'organisation_id',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function organisationDocumentVersions()
    {
        return $this->hasMany(OrganisationDocumentVersion::class);
    }
}
