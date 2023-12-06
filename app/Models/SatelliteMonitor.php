<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SatelliteMonitor extends Model
{
    use NamedEntityTrait;
    use SetAttributeByUploadTrait;
    use HasFactory;

    public $fillable = [
        'satellite_monitorable_type',
        'satellite_monitorable_id',
        'map',
        'alt_text',
    ];

    public function satellite_monitorable()
    {
        return $this->morphTo();
    }

    public function setMapAttribute($map): void
    {
        $this->setAttributeByUpload('map', $map);
    }
}
