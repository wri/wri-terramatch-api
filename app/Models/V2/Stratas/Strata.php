<?php

namespace App\Models\V2\Stratas;

use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Strata extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasTypes;

    protected $casts = [
        'published' => 'boolean',
    ];

    public $table = 'v2_stratas';

    protected $fillable = [
        'stratasable_type',
        'stratasable_id',
        'description',
        'extent',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function stratasable()
    {
        return $this->morphTo();
    }
}
