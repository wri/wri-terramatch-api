<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Jobs\UserVerificationJob;
use App\Mail\UserInvited as UserInvitedMail;
use App\Models\Organisation as OrganisationModel;
use App\Models\User as UserModel;
use App\Resources\MaskedUserResource;
use App\Resources\UserResource;
use App\Validators\UserValidator;
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

class UsersController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\User");
        $data = $request->json()->all();
        UserValidator::validate("CREATE", $data);
        $data["role"] = "user";
        $user = new UserModel($data);
        $user->saveOrFail();
        $user->refresh();
        UserVerificationJob::dispatch($user);
        return JsonResponseHelper::success(new UserResource($user), 201);
    }

    public function inviteAction(Request $request): JsonResponse
    {
        $this->authorize("invite", "App\\Models\\User");
        $data = $request->json()->all();
        UserValidator::validate("INVITE", $data);
        $me = Auth::user();
        $data["organisation_id"] = $me->organisation_id;
        $data["role"] = "user";
        $user = new UserModel($data);
        $user->saveOrFail();
        $user->refresh();
        Mail::to($user->email_address)->send(new UserInvitedMail($user->email_address, "User"));
        return JsonResponseHelper::success(new UserResource($user), 201);
    }

    public function acceptAction(Request $request): JsonResponse
    {
        $this->authorize("accept", "App\\Models\\User");
        $data = $request->json()->all();
        UserValidator::validate("ACCEPT", $data);
        $user = UserModel
            ::user()
            ->invited()
            ->where("email_address", "=", $data["email_address"])
            ->firstOrFail();
        $user->fill($data);
        $user->saveOrFail();
        UserVerificationJob::dispatch($user);
        return JsonResponseHelper::success(new UserResource($user), 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $user = UserModel
            ::user()
            ->where("id", "=", $id)
            ->firstOrFail();
        $this->authorize("read", $user);
        return JsonResponseHelper::success(new UserResource($user), 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $user = UserModel
            ::user()
            ->where("id", "=", $id)
            ->firstOrFail();
        $this->authorize("update", $user);
        $data = $request->json()->all();
        UserValidator::validate("UPDATE", $data);
        $me = Auth::user();
        $changed = array_key_exists("email_address", $data) && $data["email_address"] != $user->email_address;
        if (array_key_exists("avatar", $data)) {
            $data["avatar"] = UploadHelper::findByIdAndValidate(
                $data["avatar"], UploadHelper::IMAGES, $me->id
            );
        }
        $user->fill($data);
        $user->saveOrFail();
        if ($changed) {
            $user->email_address_verified_at = null;
            UserVerificationJob::dispatch($user);
        }
        return JsonResponseHelper::success(new UserResource($user), 200);
    }

    public function readAllByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = OrganisationModel::findOrFail($id);
        $this->authorize("read", $organisation);
        $users = UserModel
            ::user()
            ->accepted()
            ->verified()
            ->where("organisation_id", "=", $organisation->id)
            ->orderBy("last_name", "asc")
            ->orderBy("first_name", "asc")
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
        $this->authorize("inspect", $organisation);
        $users = UserModel
            ::user()
            ->where("organisation_id", "=", $organisation->id)
            ->orderBy("last_name", "asc")
            ->orderBy("first_name", "asc")
            ->get();
        $resources = [];
        foreach ($users as $user) {
            $resources[] = new UserResource($user);
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
        $user = UserModel::where("id", "=", $id)->accepted()->verified()->user()->firstOrFail();
        $user->is_subscribed = false;
        $user->saveOrFail();
        $url = Config::get("app.front_end") . "/unsubscribe";
        return Redirect::to($url);
    }
}
