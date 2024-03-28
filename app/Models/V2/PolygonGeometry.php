<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\V2\CriteriaSite;

class PolygonGeometry extends Model
{
    use SoftDeletes;

    protected $table = 'polygon_geometry';

    protected $fillable = [
        'uuid', 'polygon_id', 'geom'
    ];
    public function criteriaSite()
    {
        return $this->hasOne(CriteriaSite::class, 'polygon_id', 'polygon_id');
    }
}
