<?php

namespace App\Models;

use App\Models\Traits\HasVersions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestorationMethodMetric extends Model
{
    use SoftDeletes,
        HasVersions;

    protected $versionClass = RestorationMethodMetricVersion::class;

    public $guarded = [];
    public $timestamps = false;
}
