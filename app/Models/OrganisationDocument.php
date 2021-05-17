<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasVersions;

class OrganisationDocument extends Model
{
    use SoftDeletes, HasVersions, NamedEntityTrait;

    protected $versionClass = "App\\Models\\OrganisationDocumentVersion";

    public $guarded = [];
}
