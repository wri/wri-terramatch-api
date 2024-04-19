<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Jobs\UserVerificationJob;
use App\Models\User as UserModel;
use App\Resources\UserResource;
use App\Validators\UserValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UsersRoleController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        Log::info($request);
        $this->authorize('create', \App\Models\User::class);
        $url = $request->get('callback_url') ? $request->get('callback_url') : null;
        $request->request->remove('callback_url');
        $data = $request->json()->all();
        UserValidator::validate('CREATE', $data);
        $data['role'] = 'user';
        $user = new UserModel($data);
        $user->saveOrFail();
        $user->refresh();

        assignSpatieRole($user);

        UserVerificationJob::dispatch($user, $url);

        return JsonResponseHelper::success(new UserResource($user), 201);
    }
}
