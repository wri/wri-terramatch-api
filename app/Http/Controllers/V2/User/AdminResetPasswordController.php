<?php

namespace App\Http\Controllers\V2\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\User\ResetPasswordRequest;
use App\Models\V2\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AdminResetPasswordController extends Controller
{
    public function __invoke(User $user, ResetPasswordRequest $request): JsonResponse
    {
        $this->authorize('resetPassword', User::class);

        $user->password = Hash::make($request->get('password'));
        $user->save();

        return new JsonResponse('Password Updated', 200);
    }
}
