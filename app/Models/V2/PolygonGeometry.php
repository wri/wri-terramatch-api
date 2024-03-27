<?php

namespace App\Models\V2;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PolygonGeometry extends Model
{
    use SoftDeletes;

    protected $table = 'polygon_geometry';

    protected $fillable = [
        'uuid', 'polygon_id', 'geom'
    ];
}
