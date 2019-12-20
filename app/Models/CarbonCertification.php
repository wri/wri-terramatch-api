<?php

namespace App\Models;

use App\Models\Traits\HasVersions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarbonCertification extends Model
{
    use SoftDeletes,
        HasVersions;

    protected $versionClass = CarbonCertificationVersion::class;

    public $guarded = [];
    public $timestamps = false;
}
