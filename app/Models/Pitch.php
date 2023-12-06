<?php

namespace App\Models;

use App\Models\Traits\HasVersions;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;

class Pitch extends Model
{
    use HasVersions;
    use NamedEntityTrait;

    protected $versionClass = \App\Models\PitchVersion::class;

    public $fillable = [
        'organisation_id',
        'visibility',
        'visibility_updated_at',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function carbonCertifications()
    {
        return $this->hasMany(CarbonCertification::class);
    }
}
