<?php

namespace App\Models;

use App\Models\Interfaces\Version;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestorationMethodMetricVersion extends Model implements Version
{
    use NamedEntityTrait, SoftDeletes, IsVersion;

    protected $parentClass = "App\\Models\\RestorationMethodMetric";

    public $guarded = [];
    public $dates = [
        "approved_rejected_at"
    ];
    public $casts = [
        "species_impacted" => "array"
    ];
}
