<?php

namespace App\Models;

use App\Models\Traits\InvitedAcceptedAndVerifiedScopesTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use App\Models\Traits\SetAttributeDynamicallyTrait;
use Illuminate\Database\Eloquent\Builder;

class Admin extends Model
{
    public $table = "users";
    public $timestamps = false;
    public $guarded = [];
    public $dates = [
        "last_logged_in_at",
        "email_address_verified_at"
    ];

    public function setPasswordAttribute(string $password): void
    {
        if (Hash::needsRehash($password)) {
            $password = Hash::make($password);
        }
        $this->attributes["password"] = $password;
    }

    use SetAttributeDynamicallyTrait;

    public function setAvatarAttribute($avatar): void
    {
        $this->setAttributeDynamically("avatar", $avatar);
    }

    public function scopeAdmin(Builder $query): Builder
    {
        return $query->where("role", "=", "admin");
    }

    use InvitedAcceptedAndVerifiedScopesTrait;
}
