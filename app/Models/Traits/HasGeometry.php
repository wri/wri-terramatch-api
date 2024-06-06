<?php

namespace App\Models\Traits;

/**
 * @method static isUuid($uuid)
 * @property mixed $uuid
 */
trait HasGeometry
{
    public static function getGeoJson(string $uuid): ?array
    {
        $geojson_string = static::isUuid($uuid)
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
        return static::isUuid($uuid)
            ->selectRaw('ST_GeometryType(geom) as geometry_type_string')
            ->first()
            ?->geometry_type_string;
    }

    public function getGeometryTypeAttribute(): string
    {
        return self::getGeometryType($this->uuid);
    }

    public static function getDbGeometry(string $uuid)
    {
        return static::isUuid($uuid)
            ->selectRaw('ST_Area(geom) AS area, ST_Y(ST_Centroid(geom)) AS latitude')
            ->first();
    }

    public function getDbGeometryAttribute()
    {
        return self::getDbGeometry($this->uuid);
    }
}
