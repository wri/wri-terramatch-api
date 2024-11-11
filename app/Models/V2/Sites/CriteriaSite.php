<?php

namespace App\Models\V2\Sites;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class CriteriaSite extends Model
{
    use HasUuid;
    use SoftDeletes;

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
        'is_active'
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
    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }
    public function scopeForCriteria($query, $criteriaId)
    {
        return $query->where('criteria_id', $criteriaId)
                     ->active();
    }
    
}
