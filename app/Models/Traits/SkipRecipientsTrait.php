<?php

namespace App\Models\Traits;

use Illuminate\Support\Collection;

trait SkipRecipientsTrait
{
    public function skipRecipients(Collection $users): Collection
    {
        $skipRecipients = collect(explode(',', getenv('ENTITY_UPDATE_DO_NOT_EMAIL')));

        return $users->filter(function ($user) use ($skipRecipients) {
            return ! $skipRecipients->contains($user->email_address ?? $user['email_address']);
        });
    }
}
