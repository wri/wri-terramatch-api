<?php

namespace App\Http\Controllers;

use App\Exceptions\FailedLoginException;
use App\Exceptions\SamePasswordException;
use App\Helpers\JsonResponseHelper;
use App\Jobs\ResetPasswordJob;
use App\Jobs\UserVerificationJob;
use App\Models\PasswordReset as PasswordResetModel;
use App\Models\User as UserModel;
use App\Models\Verification as VerificationModel;
use App\Validators\AuthValidator;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginAction(Request $request): JsonResponse
    {
        $this->authorize("login", "App\\Models\\Auth");
        $data = $request->json()->all();
        AuthValidator::validate("LOGIN", $data);
        $token = Auth::attempt($data);
        if (!$token) {
            throw new FailedLoginException();
        }
        $me = Auth::user();
        if (is_null($me->password)) {
            throw new FailedLoginException();
        }
        $me->last_logged_in_at = new DateTime("now", new DateTimeZone("UTC"));
        $me->saveOrFail();
        return JsonResponseHelper::success((object) ["token" => $token], 200);
    }

    public function logoutAction(Request $request): JsonResponse
    {
        $this->authorize("logout", "App\\Models\\Auth");
        Auth::logout();
        return JsonResponseHelper::success((object) [], 200);
    }

    public function refreshAction(Request $request): JsonResponse
    {
        $this->authorize("refresh", "App\\Models\\Auth");
        $token = Auth::refresh();
        return JsonResponseHelper::success((object) ["token" => $token], 200);
    }

    public function resendAction(Request $request): JsonResponse
    {
        $this->authorize("resend", "App\\Models\\Auth");
        $me = Auth::user();
        UserVerificationJob::dispatch($me);
        return JsonResponseHelper::success((object) [], 200);
    }

    public function verifyAction(Request $request): JsonResponse
    {
        $this->authorize("verify", "App\\Models\\Auth");
        $data = $request->json()->all();
        AuthValidator::validate("VERIFY", $data);
        $me = Auth::user();
        $verification = VerificationModel
            ::where("token", "=", $data["token"])
            ->where("user_id", "=", $me->id)
            ->firstOrFail();
        $me->email_address_verified_at = new DateTime("now", new DateTimeZone("UTC"));
        $me->saveOrFail();
        $verification->delete();
        return JsonResponseHelper::success((object) [], 200);
    }

    public function resetAction(Request $request): JsonResponse
    {
        $this->authorize("reset", "App\\Models\\Auth");
        $data = $request->json()->all();
        AuthValidator::validate("RESET", $data);
        try {
            $user = UserModel
                ::where("email_address", "=", $data["email_address"])
                ->whereNotNull("password")
                ->firstOrFail();
        } catch (Exception $exception) {
            return JsonResponseHelper::success((object) [], 200);
        }
        ResetPasswordJob::dispatch($user);
        return JsonResponseHelper::success((object) [], 200);
    }

    public function changeAction(Request $request): JsonResponse
    {
        $this->authorize("change", "App\\Models\\Auth");
        $data = $request->json()->all();
        AuthValidator::validate("CHANGE", $data);
        $passwordReset = PasswordResetModel::where("token", "=", $data["token"])->firstOrFail();
        $user = UserModel::findOrFail($passwordReset->user_id);
        if (Hash::check($data["password"], $user->password)) {
            throw new SamePasswordException();
        }
        $user->password = $data["password"];
        $user->saveOrFail();
        $passwordReset->delete();
        return JsonResponseHelper::success((object) [], 200);
    }

    public function meAction(Request $request): JsonResponse
    {
        $this->authorize("me", "App\\Models\\Auth");
        $me = Auth::user();
        $classes = [
            "App\\Models\\" . ucfirst($me->role),
            "App\\Resources\\" . ucfirst($me->role) . "Resource"
        ];
        $model = $classes[0]::findOrFail($me->id);
        $resource = new $classes[1]($model);
        return JsonResponseHelper::success($resource, 200);
    }
}
