<?php

namespace App\Models\V2;

use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invasive extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use HasTypes;

    public $table = 'v2_invasives';

    protected $fillable = [
        'name',
        'type',
        'collection',
        'invasiveable_type',
        'invasiveable_id',

        'old_id',
        'old_model',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function invasiveable()
    {
        return $this->morphTo();
    }
}
