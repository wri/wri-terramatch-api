<?php

namespace App\Models\V2;

use App\Models\Traits\HasGeometry;
use App\Models\Traits\HasUuid;
use App\Models\V2\Sites\CriteriaSite;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PolygonGeometry extends Model
{
    use HasUuid;
    use SoftDeletes;
    use HasFactory;
    use HasGeometry;

    protected $table = 'polygon_geometry';

    protected $fillable = [
        'polygon_id',
        'geom',
        'created_by',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function criteriaSite(): HasMany
    {
        return $this->hasMany(CriteriaSite::class, 'polygon_id', 'uuid');
    }

    public function sitePolygon(): BelongsTo
    {
        return $this->belongsTo(SitePolygon::class, 'uuid', 'poly_id');
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }
    public function point(): HasOneThrough
    {
        return $this->hasOneThrough(
            PointGeometry::class,
            SitePolygon::class,
            'poly_id', // Foreign Key on SitePolygon table to Polygon
            'uuid', // Foreign Key on Points table
            'uuid', // Local Key on Polygon table
            'point_id' // Local Key on SitePolygon table
        );
    }

    // New convenience method
    public function deleteWithRelated()
    {
        DB::transaction(function () {
            if ($this->sitePolygon) {
                if ($this->sitePolygon->point) {
                    $this->sitePolygon->point->delete();
                }
                $this->sitePolygon->delete();
            }
            $this->delete();
        });
    }
}
