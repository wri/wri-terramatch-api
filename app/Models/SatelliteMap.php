<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Model;

class SatelliteMap extends Model
{
    use NamedEntityTrait;

    public $guarded = [];

    public function monitoring()
    {
        return $this->belongsTo("App\\Models\\Monitoring", "monitoring_id", "id");
    }

    use SetAttributeByUploadTrait;

    public function setMapAttribute($map): void
    {
        $this->setAttributeByUpload("map", $map);
    }
}
