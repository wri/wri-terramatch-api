<?php

namespace App\Models;

use App\Models\Contracts\NamedEntity;
use App\Models\Contracts\Version;
use App\Models\Traits\HasVersions;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarbonCertificationVersion extends Model implements Version, NamedEntity
{
    use NamedEntityTrait,
        SoftDeletes,
        IsVersion;

    protected $parentClass = CarbonCertification::class;

    public $guarded = [];
    public $dates = [
        "approved_rejected_at"
    ];
    public $timestamps = false;

    public function setLinkAttribute($link): void
    {
        $this->attributes["link"] = repair_url($link);
    }
}
