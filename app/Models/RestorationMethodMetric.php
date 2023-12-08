<?php

namespace App\Models;

use App\Models\Traits\HasVersions;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestorationMethodMetric extends Model
{
    use SoftDeletes;
    use HasVersions;
    use NamedEntityTrait;

    protected $versionClass = \App\Models\RestorationMethodMetricVersion::class;

    public $fillable = [
        'pitch_id',
    ];

    public function pitch()
    {
        return $this->belongsTo(Pitch::class);
    }
}
