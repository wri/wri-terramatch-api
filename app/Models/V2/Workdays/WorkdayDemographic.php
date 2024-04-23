<?php

namespace App\Models\V2\Workdays;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkdayDemographic extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'workday_id',
        'demographic_type',
        'demographic_value',
        'amount',
    ];

    // demographic types
    public const GENDER = 'gender';
    public const AGE = 'age';
    public const ETHNICITY = 'ethnicity';

    public function workday(): BelongsTo
    {
        return $this->belongsTo(Workday::class);
    }
}
