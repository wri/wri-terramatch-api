<?php

namespace App\Models\V2;

use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoreTeamLeader extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;

    public $table = 'v2_core_team_leaders';

    protected $fillable = [
        'organisation_id',
        'first_name',
        'last_name',

        'position',
        'gender',
        'age',
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
