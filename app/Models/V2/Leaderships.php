<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leaderships extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;

    public $table = 'leaderships';

    protected $fillable = [
        'organisation_id',
        'collection',
        'first_name',
        'last_name',

        'position',
        'gender',
        'age',
        'nationality',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id', 'id');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
