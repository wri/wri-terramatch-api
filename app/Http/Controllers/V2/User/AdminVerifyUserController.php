<?php

namespace App\Http\Controllers\V2\User;

use App\Http\Controllers\Controller;
use App\Models\V2\User;
use Illuminate\Http\JsonResponse;

class AdminVerifyUserController extends Controller
{
    public function __invoke(User $user): JsonResponse
    {
        $this->authorize('verify', User::class);

        if (empty($user->id)) {
            return new JsonResponse('No user found.', 404);
        }

        $user->email_address_verified_at = now();
        $user->save();

        return new JsonResponse('User verified.', 200);
    }
}
