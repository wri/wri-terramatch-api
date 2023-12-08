<?php

namespace App\Models;

use App\Models\Traits\HasUuid;
use App\Models\Traits\InvitedAcceptedAndVerifiedScopesTrait;
use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Admin extends Model
{
    use NamedEntityTrait;

    use HasUuid;

    use SetAttributeByUploadTrait;

    use InvitedAcceptedAndVerifiedScopesTrait;

    public $table = 'users';

    public $timestamps = false;

    public $guarded = [];

    protected $casts = [
        'last_logged_in_at' => 'datetime',
        'email_address_verified_at' => 'datetime',
        'is_subscribed' => 'boolean',
        'has_consented' => 'boolean',
    ];

    public function setPasswordAttribute(string $password): void
    {
        if (Hash::needsRehash($password)) {
            $password = Hash::make($password);
        }
        $this->attributes['password'] = $password;
    }

    public function setAvatarAttribute($avatar): void
    {
        $this->setAttributeByUpload('avatar', $avatar);
    }

    public function scopeAdmin(Builder $query): Builder
    {
        return $query->where('role', '=', 'admin');
    }

    public function scopeTerrafundAdminOrAdmin(Builder $query): Builder
    {
        return $query->where('role', 'admin')
            ->orWhere('role', 'terrafund_admin');
    }
}
