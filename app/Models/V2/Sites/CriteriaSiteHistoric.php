<?php

namespace App\Models\V2\Sites;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CriteriaSiteHistoric extends Model
{
    use HasUuid;
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'criteria_site_historic';

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
        'deleted_at',
        'date_created',
    ];

    public function scopeForCriteria($query, $criteriaId)
    {
        return $query->where('criteria_id', $criteriaId);
    }
}
