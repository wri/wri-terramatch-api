<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Resources\V2\User\UserResource;
use App\Jobs\UserVerificationJob;
use App\Mail\UserInvited as UserInvitedMail;
use App\Models\Organisation as OrganisationModel;
use App\Models\V2\User;
use App\Resources\MaskedUserResource;
use App\Validators\UserValidator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize('create', User::class);
        $url = $request->get('callback_url') ? $request->get('callback_url') : null;
        $request->request->remove('callback_url');
        $data = $request->json()->all();
        UserValidator::validate('CREATE', $data);
        $data['role'] = $data['primary_role'];
        $user = new User($data);
        $user->assignRole($data['primary_role']);
        $user->saveOrFail();
        $user->refresh();

        assignSpatieRole($user);

        UserVerificationJob::dispatch($user, $url);

        return JsonResponseHelper::success(new UserResource($user), 201);
    }

    public function inviteAction(Request $request): JsonResponse
    {
        $url = $request->get('callback_url') ? $request->get('callback_url') : null;
        $request->request->remove('callback_url');
        $this->authorize('invite', 'App\\Models\\V2\\User');
        $data = $request->json()->all();
        UserValidator::validate('INVITE', $data);
        $me = Auth::user();
        $data['organisation_id'] = $me->organisation_id;
        if (! isset($data['role'])) {
            $data['role'] = 'user';
        }
        if ($data['role'] == 'terrafund_admin') {
            $this->authorize('inviteTerrafundAdmin', \App\Models\Admin::class);
        }
        $user = new User($data);
        $user->saveOrFail();
        $user->refresh();
        Mail::to($user->email_address)->send(new UserInvitedMail($user->email_address, 'User', $url));

        return JsonResponseHelper::success(new UserResource($user), 201);
    }

    public function acceptAction(Request $request): JsonResponse
    {
        $this->authorize('accept', User::class);
        $url = $request->get('callback_url') ? $request->get('callback_url') : null;
        $request->request->remove('callback_url');
        $data = $request->json()->all();
        UserValidator::validate('ACCEPT', $data);
        $user = User::userOrTerrafundAdmin()
            ->invited()
            ->where('email_address', '=', $data['email_address'])
            ->firstOrFail();
        $user->fill($data);
        $user->saveOrFail();
        UserVerificationJob::dispatch($user, $url);

        return JsonResponseHelper::success(new UserResource($user), 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $user = User::user()
            ->where('id', $id)
            ->firstOrFail();
        $this->authorize('read', $user);

        return JsonResponseHelper::success(new UserResource($user), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', User::class);

        $users = User::user()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(30);
        $resources = [];
        foreach ($users as $user) {
            $resources[] = new UserResource($user);
        }

        $meta = (object)[
            'first' => $users->firstItem(),
            'current' => $users->currentPage(),
            'last' => $users->lastPage(),
            'total' => $users->total(),
        ];

        return JsonResponseHelper::success($resources, 200, $meta);
    }

    public function readAllUnverifiedAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', User::class);

        $users = User::user()
            ->unverified()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(30);

        $resources = [];
        foreach ($users as $user) {
            $resources[] = new UserResource($user);
        }

        $meta = (object)[
            'first' => $users->firstItem(),
            'current' => $users->currentPage(),
            'last' => $users->lastPage(),
            'total' => $users->total(),
        ];

        return JsonResponseHelper::success($resources, 200, $meta);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $user = User::user()
            ->where('id', $id)
            ->firstOrFail();
        $this->authorize('update', $user);
        $url = $request->get('callback_url') ? $request->get('callback_url') : null;
        $request->request->remove('callback_url');
        $data = $request->json()->all();
        UserValidator::validate('UPDATE', $data);
        $me = Auth::user();
        $changed = array_key_exists('email_address', $data) && $data['email_address'] != $user->email_address;
        if (array_key_exists('avatar', $data)) {
            $data['avatar'] = UploadHelper::findByIdAndValidate(
                $data['avatar'],
                UploadHelper::IMAGES,
                $me->id
            );
        }
        $user->fill($data);
        $user->saveOrFail();
        if ($changed) {
            $user->email_address_verified_at = null;
            UserVerificationJob::dispatch($user, $url);
        }

        assignSpatieRole($user);

        return JsonResponseHelper::success(new UserResource($user), 200);
    }

    public function updateRoleAction(Request $request, int $userId): JsonResponse
    {
        $user = User::where('id', $userId)->firstOrFail();
        $this->authorize('updateRole', $user);
        $data = $request->json()->all();
        UserValidator::validate('UPDATE_ROLE', $data);

        $user->role = $data['role'];
        $user->saveOrFail();

        assignSpatieRole($user);

        return JsonResponseHelper::success(new UserResource($user), 200);
    }

    public function resendVerificationEmailAction(Request $request): JsonResponse
    {
        $url = $request->get('callback_url') ? $request->get('callback_url') : null;
        $request->request->remove('callback_url');
        $data = $request->json()->all();
        UserValidator::validate('RESEND', $data);
        $user = User::isUuid($data['uuid'])->firstOrFail();

        $this->authorize('resend', $user);
        UserVerificationJob::dispatch($user, $url);

        return JsonResponseHelper::success((object) [], 200);
    }

    public function readAllByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = OrganisationModel::findOrFail($id);
        $this->authorize('read', $organisation);
        $users = User::user()
            ->accepted()
            ->verified()
            ->where('organisation_id', '=', $organisation->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        $resources = [];
        foreach ($users as $user) {
            $resources[] = new MaskedUserResource($user);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function inspectByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = OrganisationModel::findOrFail($id);
        $this->authorize('inspect', $organisation);
        $users = User::user()
            ->where('organisation_id', '=', $organisation->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        $resources = [];
        foreach ($users as $user) {
            $resources[] = new UserResource($user);
        }

        return JsonResponseHelper::success($resources, 200);
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
