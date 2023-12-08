<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundingType extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;

    public $table = 'v2_funding_types';

    protected $fillable = [
        'organisation_id',
        'source',
        'amount',
        'year',
        'type',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id', 'uuid');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
