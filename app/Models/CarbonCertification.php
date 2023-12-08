<?php

namespace App\Models;

use App\Models\Traits\HasVersions;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarbonCertification extends Model
{
    use SoftDeletes;
    use HasVersions;
    use NamedEntityTrait;

    protected $versionClass = \App\Models\CarbonCertificationVersion::class;

    public $guarded = [];

    public function pitch()
    {
        return $this->belongsTo(Pitch::class);
    }

    public function carbonCertificationVersions()
    {
        return $this->hasMany(CarbonCertificationVersion::class);
    }
}
