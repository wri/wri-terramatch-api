<?php

namespace App\Models\V2\Sites;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CriteriaSite extends Model
{
    use HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'criteria_site';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'criteria_id',
        'polygon_id',
        'valid',
        'extra_info',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'date_created',
    ];

    public function scopeForCriteria($query, $criteriaId)
    {
        return $query->where('criteria_id', $criteriaId);
    }
}
