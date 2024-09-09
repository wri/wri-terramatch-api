<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UserLocaleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserLocaleController extends Controller
{
    public function __invoke(UserLocaleRequest $request, string $locale): JsonResponse
    {
        $user = Auth::user();
        $user->locale = $locale;
        $user->saveOrFail();

        return response()->json([
            'message' => 'User locale updated successfully',
        ]);
    }
}
