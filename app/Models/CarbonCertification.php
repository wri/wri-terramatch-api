<?php

namespace App\Models;

use App\Models\Traits\HasVersions;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarbonCertification extends Model
{
    use SoftDeletes, HasVersions, NamedEntityTrait;

    protected $versionClass = "App\\Models\\CarbonCertificationVersion";

    public $guarded = [];
}
