<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait InvitedAcceptedAndVerifiedScopesTrait
{
    public function scopeInvited(Builder $query): Builder
    {
        return $query->whereNull("password");
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->whereNotNull("password");
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull("email_address_verified_at");
    }
}