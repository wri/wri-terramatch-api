<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\User as UserModel;
use App\Resources\UserRoleResource;
use App\Validators\UserRoleValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsersRoleController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize('create', UserModel::class);
        $data = $request->json()->all();
        UserRoleValidator::validate('CREATE', $data);
        $data['role'] = $data['primary_role'];
        $user = new UserModel($data);
        $user->assignRole($data['primary_role']);
        $user->saveOrFail();
        $user->refresh();

        return JsonResponseHelper::success(new UserRoleResource($user), 201);
    }
}
