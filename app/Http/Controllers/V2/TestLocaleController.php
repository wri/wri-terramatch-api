<?php

namespace App\Http\Controllers\V2;

use App\Models\V2\I18n\I18nItem;
use App\Models\V2\LocalizationKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Mail\ApplicationSubmittedConfirmation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;


class TestLocaleController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $localizationKey = LocalizationKey::where('key', 'application-submitted-confirmation.subject')
            ->first();
        $user = Auth::user();
        App::setLocale($user->locale);
        return response()->json([
            'message' =>  $localizationKey->translated_value
        ]);
    }
}
