<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use App\Models\V2\Sites\CriteriaSite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
}
