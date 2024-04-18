<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use App\Models\V2\Sites\CriteriaSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PolygonGeometry extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $table = 'polygon_geometry';

    protected $fillable = [
        'polygon_id', 'geom',
    ];

    public function criteriaSite()
    {
        return $this->hasMany(CriteriaSite::class, 'polygon_id', 'polygon_id');
    }

    public function getDbGeometryAttribute()
    {
        $result = DB::selectOne(
            '
            SELECT ST_Area(geom) AS area, ST_Y(ST_Centroid(geom)) AS latitude
            FROM polygon_geometry
            WHERE uuid = :uuid',
            ['uuid' => $this->uuid]
        );

        return $result;
    }
}
