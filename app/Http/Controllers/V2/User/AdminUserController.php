<?php

namespace App\Http\Controllers\V2\User;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\User\StoreUserRequest;
use App\Http\Requests\V2\User\UpdateUserRequest;
use App\Http\Resources\V2\User\UserResource;
use App\Http\Resources\V2\User\UsersCollection;
use App\Models\User as V1User;
use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminUserController extends Controller
{
    public function index(Request $request): UsersCollection
    {
        $this->authorize('readAll', User::class);
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'last_logged_in_at','-last_logged_in_at',
            'created_at', '-created_at',
            'first_name', '-first_name',
            'last_name', '-last_name',
            'email_address', '-email_address',
            'name', '-name',
            'organisation_name', '-organisation_name',
            'organisation_uuid', '-organisation_uuid',
            'email_address_verified_at', '-email_address_verified_at',
        ];

        $qry = QueryBuilder::for(User::class)
            ->with(['organisations'])
            ->selectRaw('
                users.*,
                (SELECT uuid FROM organisations WHERE id = organisation_id) as organisation_uuid,
                (SELECT name FROM organisations WHERE id = organisation_id) as organisation_name
            ')
            ->allowedFilters([
                AllowedFilter::scope('verified'),
                AllowedFilter::exact('organisation_id'),
                AllowedFilter::scope('organisation_uuid'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if ($request->query('search')) {
            $ids = User::search(trim($request->query('search')))->get()->pluck('id')->toArray();

            if (empty($ids)) {
                return new UsersCollection([]);
            }
            $qry->whereIn('id', $ids);
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new UsersCollection($collection);
    }

    public function show(User $user, Request $request): UserResource
    {
        $this->authorize('read', $user);

        return new UserResource($user);
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $data = $request->all();

        switch ($request->get('primary_role')) {
            case 'admin-super':
            case 'admin-ppc':
                $data['role'] = 'admin';

                break;
            case 'admin-terrafund':
                $data['role'] = 'terrafund_admin';

                break;
            case 'project-developer':
                $data['role'] = 'user';

                break;

            case 'project-manager':
                $data['role'] = 'project-manager';

                break;
        }

        $user = User::create($data);

        if (! empty($request->get('primary_role')) && Auth::user()->hasRole('admin-super')) {
            $user->syncRoles([$request->get('primary_role')]);
        } else {
            assignSpatieRole($user);
        }

        return new UserResource($user);
    }

    public function update(User $user, UpdateUserRequest $request)
    {
        $this->authorize('update', $user);

        $email = $request->get('email_address');
        if (! empty($email) && $email !== $user->email_address) {
            $validator = Validator::make($request->all(), [ 'email_address' => 'required|string|email|between:1,255|unique:users,email_address']);
            $validator->validate();
        }

        $data = $request->all();

        if (! empty($request->get('primary_role')) && (Auth::user()->hasRole('admin-super') || Auth::user()->role === 'admin')) {
            $v1User = V1User::find($user->id);
            $v1User->syncRoles([$request->get('primary_role')]);
            $user->syncRoles([$request->get('primary_role')]);

            switch ($request->get('primary_role')) {
                case 'admin-super':
                case 'admin-ppc':
                    $data['role'] = 'admin';

                    break;
                case 'admin-terrafund':
                    $data['role'] = 'terrafund_admin';

                    break;
                case 'project-developer':
                    $data['role'] = 'project-developer';

                    break;
                case 'project-manager':
                    $data['role'] = 'project-manager';

                    break;
                case 'government':
                    $data['role'] = 'government';

                    break;
                case 'funder':
                    $data['role'] = 'funder';

                    break;
            }
        }

        $user->update($data);

        if ($request->get('organisation')) {
            $organisation_id = Organisation::isUuid($request->get('organisation'))
                ->pluck('id')
                ->first();
            if ($organisation_id) {
                $user->organisation_id = $organisation_id;
                $user->save();
            }
        }

        if (is_array($request->get('monitoring_organisations'))) {
            $orgUuids = $request->get('monitoring_organisations');

            if (count($orgUuids) >= 1) {
                $organisation_ids = Organisation::whereIn('uuid', $request->get('monitoring_organisations'))
                    ->pluck('id')
                    ->toArray();
                $user->organisations()->sync($organisation_ids);
            } else {
                $user->organisations()->detach();
            }
        }

        return new UserResource($user);
    }

    public function destroy(User $user, Request $request): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return JsonResponseHelper::success(['User has been deleted.'], 200);
    }
}
