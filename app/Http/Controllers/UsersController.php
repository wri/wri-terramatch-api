<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Resources\V2\User\UserResource;
use App\Jobs\UserVerificationJob;
use App\Models\V2\User;
use App\Validators\UserValidator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class UsersController extends Controller
{
    public const DEFAULT_USER_ROLE = 'project-developer';
    public const USER_SELECTABLE_ROLES = [self::DEFAULT_USER_ROLE, 'funder', 'government'];

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);
        $url = $request->get('callback_url') ? $request->get('callback_url') : null;
        $request->request->remove('callback_url');
        $data = $request->json()->all();
        UserValidator::validate('CREATE', $data);
        $user = new User($data);
        $user->saveOrFail();

        $role = $data['role'];
        if (! in_array($role, self::USER_SELECTABLE_ROLES)) {
            // Default to PD if a role is sent that is not valid for a user to select for themselves.
            $role = self::DEFAULT_USER_ROLE;
        }
        $user->assignRole($role);

        UserVerificationJob::dispatch($user, $url);

        return JsonResponseHelper::success(new UserResource($user), 201);
    }

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
