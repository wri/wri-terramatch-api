<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\AcceptAdminRequest;
use App\Http\Requests\InviteAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Jobs\UserVerificationJob;
use App\Mail\UserInvited as UserInvitedMail;
use App\Models\Admin as AdminModel;
use App\Resources\AdminResource;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class AdminsController extends Controller
{
    public function inviteAction(InviteAdminRequest $request): JsonResponse
    {
        $url = $request->get('callback_url') ? $request->get('callback_url') : null;
        $this->authorize('invite', 'App\\Models\\Admin');
        $data = Arr::except($request->json()->all(), ['callback_url']);

        $extra = [
            'role' => 'admin',
        ];
        $admin = AdminModel::create(array_merge($data, $extra));

        Mail::to($admin->email_address)->send(new UserInvitedMail($admin->email_address, 'Admin', $url));

        return JsonResponseHelper::success(new AdminResource($admin), 201);
    }

    public function acceptAction(AcceptAdminRequest $request): JsonResponse
    {
        $this->authorize('accept', \App\Models\Admin::class);
        $url = $request->get('callback_url') ? $request->get('callback_url') : null;
        $request->request->remove('callback_url');
        $data = $request->json()->all();
        $admin = AdminModel::admin()
            ->invited()
            ->where('email_address', '=', $data['email_address'])
            ->firstOrFail();

        $admin->update($data);

        UserVerificationJob::dispatch($admin, $url);

        return JsonResponseHelper::success(new AdminResource($admin), 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $admin = AdminModel::admin()
            ->where('id', '=', $id)
            ->firstOrFail();
        $this->authorize('read', $admin);

        return JsonResponseHelper::success(new AdminResource($admin), 200);
    }

    public function updateAction(UpdateAdminRequest $request, int $id): JsonResponse
    {
        $admin = AdminModel::admin()
            ->where('id', '=', $id)
            ->firstOrFail();
        $this->authorize('update', $admin);
        $url = $request->get('callback_url') ? $request->get('callback_url') : null;
        $request->request->remove('callback_url');
        $data = $request->json()->all();
        $me = Auth::user();
        $changed = array_key_exists('email_address', $data) && $data['email_address'] != $admin->email_address;
        if (array_key_exists('avatar', $data)) {
            $data['avatar'] = UploadHelper::findByIdAndValidate(
                $data['avatar'],
                UploadHelper::IMAGES,
                $me->id
            );
        }
        $admin->update($data);

        if ($changed) {
            $admin->email_address_verified_at = null;
            UserVerificationJob::dispatch($admin, $url);
        }

        return JsonResponseHelper::success(new AdminResource($admin), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', \App\Models\Admin::class);
        $admins = AdminModel::terrafundAdminOrAdmin()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
        $resources = [];
        foreach ($admins as $admin) {
            $resources[] = new AdminResource($admin);
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
        $admin = AdminModel::where('id', '=', $id)->accepted()->verified()->admin()->firstOrFail();
        $admin->is_subscribed = false;
        $admin->saveOrFail();
        $url = config('app.front_end') . '/unsubscribe';

        return redirect()->to($url);
    }
}
