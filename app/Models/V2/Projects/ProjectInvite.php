<?php

namespace App\Models\V2\Projects;

use App\Models\Traits\HasUuid;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectInvite extends Model
{
    use HasFactory;
    use HasUuid;

    public $table = 'v2_project_invites';

    protected $fillable = [
        'uuid',
        'project_id',
        'email_address',
        'token',
        'accepted_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'email_address', 'email_address');
    }
}
