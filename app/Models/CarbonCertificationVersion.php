<?php

namespace App\Models;

use App\Helpers\UrlHelper;
use App\Models\Interfaces\Version;
use App\Models\Traits\IsVersion;
use App\Models\Traits\NamedEntityTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CarbonCertificationVersion extends Model implements Version
{
    use NamedEntityTrait;
    use SoftDeletes;
    use IsVersion;

    protected $parentClass = \App\Models\CarbonCertification::class;

    public $fillable = [
        'carbon_certification_id',
        'type',
        'link',
        'rejected_reason',
        'rejected_reason_body',
        'approved_rejected_by',
        'approved_rejected_at',
        'other_value',
        'status',
    ];

    protected $casts = [
        'approved_rejected_at' => 'datetime',
    ];

    public function setLinkAttribute($link): void
    {
        $this->attributes['link'] = UrlHelper::repair($link);
    }

    public function carbonCertification()
    {
        return $this->belongsTo(CarbonCertification::class);
    }
}
