<?php

namespace App\Models;

use App\Models\Interfaces\Version;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestorationMethodMetricVersion extends Model implements Version
{
    use NamedEntityTrait;
    use SoftDeletes;
    use IsVersion;

    protected $parentClass = \App\Models\RestorationMethodMetric::class;

    public $fillable = [
        'restoration_method_metric_id',
        'status',
        'rejected_reason',
        'approved_rejected_by',
        'approved_rejected_at',
        'experience',
        'land_size',
        'price_per_hectare',
        'biomass_per_hectare',
        'carbon_impact',
        'species_impacted',
        'restoration_method',
        'rejected_reason_body',
    ];

    protected $casts = [
        'approved_rejected_at' => 'datetime',
        'species_impacted' => 'array',
    ];

    public function approvedRejectedBy()
    {
        return $this->belongsTo(User::class, 'approved_rejected_by');
    }

    public function restorationMethodMetric()
    {
        return $this->belongsTo(RestorationMethodMetric::class);
    }
}
