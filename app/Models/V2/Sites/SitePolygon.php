<?php

namespace App\Models\V2\Sites;

use App\Models\Traits\HasUuid;
use App\Models\V2\PolygonGeometry;
use App\Models\V2\Projects\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SitePolygon extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $table = 'site_polygon';

    protected $fillable = [
      'proj_name',
      'org_name',
      'poly_id',
      'poly_name',
      'site_id',
      'site_name',
      'project_id',
      'poly_label',
      'plantstart',
      'plantend',
      'practice',
      'target_sys',
      'distr',
      'num_trees',
      'est_area',
      'country',
    ];

    public function polygonGeometry()
    {
        return $this->belongsTo(PolygonGeometry::class, 'poly_id', 'uuid');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id', 'uuid');
    }
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id', 'uuid');
    }
}
