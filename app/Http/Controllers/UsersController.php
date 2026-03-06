<?php

namespace App\Http\Controllers;

use App\Models\V2\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class UsersController extends Controller
{
    public function unsubscribeAction(Request $request, String $encryptedId): RedirectResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');

        try {
            $id = Crypt::decryptString($encryptedId);
        } catch (Exception $exception) {
            throw new ModelNotFoundException();
        }
        $user = User::where('id', $id)->accepted()->verified()->user()->firstOrFail();
        $user->is_subscribed = false;
        $user->saveOrFail();
        $url = config('app.front_end') . '/unsubscribe';

        return redirect()->to($url);
    }
}
