<?php

namespace App\Models;

use App\Helpers\UrlHelper;
use App\Models\Traits\InvitedAcceptedAndVerifiedScopesTrait;
use App\Models\Traits\NamedEntityTrait;
use App\Models\Traits\SetAttributeByUploadTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use NamedEntityTrait, SetAttributeByUploadTrait, InvitedAcceptedAndVerifiedScopesTrait;

    public $guarded = [];
    public $dates = [
        "last_logged_in_at",
        "email_address_verified_at"
    ];
    public $casts = [
        "is_subscribed" => "boolean",
        "has_consented" => "boolean"
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
        $this->setAttributeByUpload("avatar", $avatar);
    }

    public function setFacebookAttribute($facebook): void
    {
        $this->attributes["facebook"] = UrlHelper::repair($facebook);
    }

    public function setTwitterAttribute($twitter): void
    {
        $this->attributes["twitter"] = UrlHelper::repair($twitter);
    }

    public function setInstagramAttribute($instagram): void
    {
        $this->attributes["instagram"] = UrlHelper::repair($instagram);
    }

    public function setLinkedinAttribute($linkedin): void
    {
        $this->attributes["linkedin"] = UrlHelper::repair($linkedin);
    }

    public function scopeUser(Builder $query): Builder
    {
        return $query->where("role", "=", "user");
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo("App\\Models\\Organisation");
    }
}
