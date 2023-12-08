<?php

namespace App\Http\Controllers\V2\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\User\UpdateBannerRequest;
use App\Http\Resources\V2\User\UserResource;
use Illuminate\Support\Facades\Auth;

class UpdateMyBannersController extends Controller
{
    public function __invoke(UpdateBannerRequest $updateBannerRequest): UserResource
    {
        $user = Auth::user();

        $user->update($updateBannerRequest->validated());

        return new UserResource($user);
    }
}
