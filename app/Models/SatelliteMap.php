<?php

namespace App\Models;

use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Model;

class SatelliteMap extends Model
{
    use NamedEntityTrait;
    use SetAttributeByUploadTrait;

    public $fillable = [
        'monitoring_id',
        'map',
        'alt_text',
        'created_by',
    ];

    public function monitoring()
    {
        return $this->belongsTo(Monitoring::class, 'monitoring_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function setMapAttribute($map): void
    {
        $this->setAttributeByUpload('map', $map);
    }
}
