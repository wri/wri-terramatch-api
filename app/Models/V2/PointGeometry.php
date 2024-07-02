<?php

namespace App\Models\V2;

use App\Models\Traits\HasGeometry;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PointGeometry extends Model
{
    use HasUuid;
    use SoftDeletes;
    use HasGeometry;

    protected $table = 'point_geometry';

    protected $fillable = [
        'geom',
        'est_area',
        'created_by',
        'last_modified_by',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function createdBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function lastModifiedBy(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'last_modified_by');
    }
}
