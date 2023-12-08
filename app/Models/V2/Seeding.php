<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Seeding extends Model
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;

    public $table = 'v2_seedings';

    protected $fillable = [
        'name',
        'weight_of_sample',
        'seeds_in_sample',
        'amount',
        'seedable_type',
        'seedable_id',
        'old_id',
        'old_model',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function seedable()
    {
        return $this->morphTo();
    }
}
