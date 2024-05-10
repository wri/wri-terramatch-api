<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use App\Models\V2\Sites\CriteriaSite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static isUuid($uuid)
 * @property mixed $uuid
 */
class PolygonGeometry extends Model
{
    use HasUuid;
    use SoftDeletes;
    use HasFactory;

    protected $table = 'polygon_geometry';

    protected $fillable = [
        'polygon_id', 'geom',
    ];

    public function criteriaSite()
    {
        return $this->hasMany(CriteriaSite::class, 'polygon_id', 'uuid');
    }

    public static function getGeoJson(string $uuid): ?array
    {
        $geojson_string = PolygonGeometry::isUuid($uuid)
            ->selectRaw('ST_AsGeoJSON(geom) as geojson_string')
            ->first()
            ?->geojson_string;

        return $geojson_string == null ? null : json_decode($geojson_string, true);
    }

    public function getGeoJsonAttribute(): array
    {
        return self::getGeoJson($this->uuid);
    }

    public static function getGeometryType(string $uuid): ?string
    {
        return PolygonGeometry::isUuid($uuid)
            ->selectRaw('ST_GeometryType(geom) as geometry_type')
            ->first()
            ?->geometry_type;
    }

    public function getGeometryTypeAttribute(): string
    {
        return self::getGeometryType($this->uuid);
    }

    public static function getDbGeometry(string $uuid)
    {
        return PolygonGeometry::isUuid($uuid)
            ->selectRaw('ST_Area(geom) AS area, ST_Y(ST_Centroid(geom)) AS latitude')
            ->first();
    }

    public function getDbGeometryAttribute()
    {
        return self::getDbGeometry($this->uuid);
    }
}
