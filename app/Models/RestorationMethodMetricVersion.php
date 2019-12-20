<?php

namespace App\Models;

use App\Models\Contracts\NamedEntity;
use App\Models\Contracts\Version;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestorationMethodMetricVersion extends Model implements Version, NamedEntity
{
    use NamedEntityTrait,
        SoftDeletes,
        IsVersion;

    protected $parentClass = RestorationMethodMetric::class;

    public $guarded = [];
    public $timestamps = false;
    public $dates = [
        "approved_rejected_at"
    ];
    public $casts = [
        "species_impacted" => "array"
    ];
}
