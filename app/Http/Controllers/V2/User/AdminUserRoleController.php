<?php

namespace App\Http\Controllers\V2\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\User\StoreUserRequest;
use App\Http\Resources\V2\User\UserResource;
use App\Models\V2\User;
use Illuminate\Support\Facades\Auth;

class AdminUserRoleController extends Controller
{
    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $data = $request->all();
        $role_id = $data['role_id'];
        switch ($request->get('primary_role')) {
            case 'admin-super':
            case 'admin-ppc':
                $data['role'] = 'admin';

                break;
            case 'admin-terrafund':
                $data['role'] = 'terrafund_admin';

                break;
            case 'project_developer':
                $data['role'] = 'user';

                break;
        }
        $data['role_id'] = $role_id;
        $user = User::create($data);

        if (! empty($request->get('primary_role')) && Auth::user()->hasRole('admin-super')) {
            $user->syncRoles([$request->get('primary_role')]);
        } else {
            assignSpatieRole($user);
        }

        return new UserResource($user);
    }
}