<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Jobs\UserVerificationJob;
use App\Mail\UserInvited as UserInvitedMail;
use App\Models\Admin as AdminModel;
use App\Resources\AdminResource;
use App\Validators\AdminValidator;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;

class AdminsController extends Controller
{
    public function inviteAction(Request $request): JsonResponse
    {
        $this->authorize("invite", "App\\Models\\Admin");
        $data = $request->json()->all();
        AdminValidator::validate("INVITE", $data);
        $data["role"] = "admin";
        $admin = new AdminModel($data);
        $admin->saveOrFail();
        $admin->refresh();
        Mail::to($admin->email_address)->send(new UserInvitedMail($admin->email_address, "Admin"));
        return JsonResponseHelper::success(new AdminResource($admin), 201);
    }

    public function acceptAction(Request $request): JsonResponse
    {
        $this->authorize("accept", "App\\Models\\Admin");
        $data = $request->json()->all();
        AdminValidator::validate("ACCEPT", $data);
        $admin = AdminModel
            ::admin()
            ->invited()
            ->where("email_address", "=", $data["email_address"])
            ->firstOrFail();
        $admin->fill($data);
        $admin->saveOrFail();
        UserVerificationJob::dispatch($admin);
        return JsonResponseHelper::success(new AdminResource($admin), 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $admin = AdminModel
            ::admin()
            ->where("id", "=", $id)
            ->firstOrFail();
        $this->authorize("read", $admin);
        return JsonResponseHelper::success(new AdminResource($admin), 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $admin = AdminModel
            ::admin()
            ->where("id", "=", $id)
            ->firstOrFail();
        $this->authorize("update", $admin);
        $data = $request->json()->all();
        AdminValidator::validate("UPDATE", $data);
        $me = Auth::user();
        $changed = array_key_exists("email_address", $data) && $data["email_address"] != $admin->email_address;
        if (array_key_exists("avatar", $data)) {
            $data["avatar"] = UploadHelper::findByIdAndValidate(
                $data["avatar"], UploadHelper::IMAGES, $me->id
            );
        }
        $admin->fill($data);
        $admin->saveOrFail();
        if ($changed) {
            $admin->email_address_verified_at = null;
            UserVerificationJob::dispatch($admin);
        }
        return JsonResponseHelper::success(new AdminResource($admin), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Admin");
        $admins = AdminModel
            ::admin()
            ->orderBy("last_name", "asc")
            ->orderBy("first_name", "asc")
            ->get();
        $resources = [];
        foreach ($admins as $admin) {
            $resources[] = new AdminResource($admin);
        }
        return JsonResponseHelper::success($resources, 200);
    }

    public function unsubscribeAction(Request $request, String $encryptedId): RedirectResponse
    {
        $this->authorize("yes", "App\\Models\\Default");
        try {
            $id = Crypt::decryptString($encryptedId);
        } catch (Exception $exception) {
            throw new ModelNotFoundException();
        }
        $admin = AdminModel::where("id", "=", $id)->accepted()->verified()->admin()->firstOrFail();
        $admin->is_subscribed = false;
        $admin->saveOrFail();
        $url = Config::get("app.front_end") . "/unsubscribe";
        return Redirect::to($url);
    }
}
