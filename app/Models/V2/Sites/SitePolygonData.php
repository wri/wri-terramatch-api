<?php

namespace App\Models\V2\Sites;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePolygonData extends Model
{
    protected $fillable = [
        'site_polygon_uuid',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function sitePolygon(): BelongsTo
    {
        return $this->belongsTo(SitePolygon::class);
    }
}