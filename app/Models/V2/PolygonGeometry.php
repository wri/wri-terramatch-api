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
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
