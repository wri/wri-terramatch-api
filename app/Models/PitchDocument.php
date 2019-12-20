<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\HasVersions;

class PitchDocument extends Model
{
    use SoftDeletes,
        HasVersions;

    protected $versionClass = PitchDocumentVersion::class;

    public $timestamps = false;
    public $guarded = [];
}
