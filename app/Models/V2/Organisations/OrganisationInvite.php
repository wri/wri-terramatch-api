<?php

namespace App\Models\V2\Organisations;

use App\Models\Traits\HasUuid;
use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationInvite extends Model
{
    use HasFactory;
    use HasUuid;

    public $table = 'v2_organisation_invites';

    protected $fillable = [
        'uuid',
        'organisation_id',
        'email_address',
        'token',
        'accepted_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email_address', 'email_address');
    }
}
