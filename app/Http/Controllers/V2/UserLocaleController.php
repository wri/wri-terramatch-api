<?php

namespace App\Http\Controllers\V2;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UserLocaleRequest;

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
