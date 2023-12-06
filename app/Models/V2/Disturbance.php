<?php

namespace App\Models\V2;

use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disturbance extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use HasTypes;

    public $table = 'v2_disturbances';

    protected $fillable = [
        'kind',
        'collection',
        'type',
        'intensity',
        'extent',
        'description',
        'disturbanceable_type',
        'disturbanceable_id',

        'old_id',
        'old_model',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function disturbanceable()
    {
        return $this->morphTo();
    }
}
