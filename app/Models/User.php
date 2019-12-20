<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Traits\SetAttributeDynamicallyTrait;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Traits\InvitedAcceptedAndVerifiedScopesTrait;

class User extends Authenticatable implements JWTSubject
{
    use SetAttributeDynamicallyTrait,
        InvitedAcceptedAndVerifiedScopesTrait;

    public $guarded = [];
    public $dates = [
        "last_logged_in_at",
        "email_address_verified_at"
    ];

    public function getJWTIdentifier(): string
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function setPasswordAttribute(string $password): void
    {
        if (Hash::needsRehash($password)) {
            $password = Hash::make($password);
        }
        $this->attributes["password"] = $password;
    }


    public function setAvatarAttribute($avatar): void
    {
        $this->setAttributeDynamically("avatar", $avatar);
    }

    public function setFacebookAttribute($facebook): void
    {
        $this->attributes["facebook"] = repair_url($facebook);
    }

    public function setTwitterAttribute($twitter): void
    {
        $this->attributes["twitter"] = repair_url($twitter);
    }

    public function setInstagramAttribute($instagram): void
    {
        $this->attributes["instagram"] = repair_url($instagram);
    }

    public function setLinkedinAttribute($linkedin): void
    {
        $this->attributes["linkedin"] = repair_url($linkedin);
    }

    public function scopeUser(Builder $query): Builder
    {
        return $query->where("role", "=", "user");
    }

    /**
     * @return BelongsTo
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}
