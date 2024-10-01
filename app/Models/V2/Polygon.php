<?php

namespace App\Models\V2;

use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Polygon extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use HasStatus;

    public $table = 'v2_polygons';

    protected $fillable = [
        'name',
        'area',
        'perimeter',
        'owner_id',
        'status',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
        ];
    }

    public static function search($query)
    {
        return self::select('v2_polygons.*')
            ->where('v2_polygons.name', 'like', "%$query%");
    }

    public function polygonable()
    {
        return $this->morphTo();
    }
}
