<?php

namespace App\Models;

use App\Models\Interfaces\Version;
use App\Models\Traits\IsVersion;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\UrlHelper;

class CarbonCertificationVersion extends Model implements Version
{
    use NamedEntityTrait, SoftDeletes, IsVersion;

    protected $parentClass = "App\\Models\\CarbonCertification";

    public $guarded = [];
    public $dates = [
        "approved_rejected_at"
    ];

    public function setLinkAttribute($link): void
    {
        $this->attributes["link"] = UrlHelper::repair($link);
    }
}
