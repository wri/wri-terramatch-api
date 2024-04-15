<?php

namespace App\Models\V2\Sites;

use App\Models\V2\PolygonGeometry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SitePolygon extends Model
{
    use SoftDeletes;

    protected $table = 'site_polygon';

    protected $fillable = [
      'proj_name',
      'org_name',
      'poly_id',
      'poly_name',
      'site_id',
      'site_name',
      'poly_label',
      'plantstart',
      'plantend',
      'practice',
      'target_sys',
      'distr',
      'num_trees',
      'est_area',
    ];

    public function polygonGeometry()
    {
        return $this->belongsTo(PolygonGeometry::class, 'poly_id', 'id');
    }
}
