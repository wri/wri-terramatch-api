<?php

namespace App\Http\Controllers\V2;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Mail\ApplicationSubmittedConfirmation;
use Illuminate\Support\Facades\Log;


class TestEmailController extends Controller
{
    public function __invoke(): JsonResponse
    {
        Log::info("here");
        $user = Auth::user();
        Mail::to('jlaura@vizonomy.com')->send(new ApplicationSubmittedConfirmation('this is not translated', $user));
        return response()->json([
            'message' => 'Test email sent successfully',
        ]);
    }
}
