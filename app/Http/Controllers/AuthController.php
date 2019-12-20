<?php

namespace App\Http\Controllers;

use App\Http\JsonResponseFactory;
use App\Mail\ResetPassword;
use App\Mail\UserVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthManager;
use App\Validators\AuthValidator;
use App\Models\User as UserModel;
use App\Models\Admin as AdminModel;
use App\Models\Verification as VerificationModel;
use App\Models\PasswordReset as PasswordResetModel;
use Exception;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    private $userModel = null;
    private $adminModel = null;
    private $authValidator = null;
    private $jsonResponseFactory = null;
    private $authManager = null;
    private $verificationModel = null;
    private $passwordResetModel = null;

    public function __construct(
        UserModel $userModel,
        AdminModel $adminModel,
        AuthValidator $authValidator,
        JsonResponseFactory $jsonResponseFactory,
        AuthManager $authManager,
        VerificationModel $verificationModel,
        PasswordResetModel $passwordResetModel
    ) {
        $this->userModel = $userModel;
        $this->adminModel = $adminModel;
        $this->authValidator = $authValidator;
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->authManager = $authManager;
        $this->verificationModel = $verificationModel;
        $this->passwordResetModel = $passwordResetModel;
    }

    public function loginAction(Request $request): JsonResponse
    {
        $this->authorize("login", "App\\Models\\Auth");
        $data = $request->json()->all();
        $this->authValidator->validate("login", $data);
        $token = $this->authManager->attempt($data);
        if (!$token) {
            return $this->jsonResponseFactory->error([], 401);
        }
        $me = $this->authManager->user();
        if (is_null($me->password)) {
            return $this->jsonResponseFactory->error([], 401);
        }
        $me->last_logged_in_at = new DateTime("now", new DateTimeZone("UTC"));
        $me->saveOrFail();
        return $this->jsonResponseFactory->success((object) ["token" => $token], 200);
    }

    public function logoutAction(Request $request): JsonResponse
    {
        $this->authorize("logout", "App\\Models\\Auth");
        $this->authManager->logout();
        return $this->jsonResponseFactory->success((object) [], 200);
    }

    public function refreshAction(Request $request): JsonResponse
    {
        $this->authorize("refresh", "App\\Models\\Auth");
        $token = $this->authManager->refresh();
        return $this->jsonResponseFactory->success((object) ["token" => $token], 200);
    }

    public function resendAction(Request $request): JsonResponse
    {
        $this->authorize("resend", "App\\Models\\Auth");
        $me = $this->authManager->user();
        Mail::to($me->email_address)->send(new UserVerification($me));
        return $this->jsonResponseFactory->success((object) [], 200);
    }

    public function verifyAction(Request $request): JsonResponse
    {
        $this->authorize("verify", "App\\Models\\Auth");
        $data = $request->json()->all();
        $this->authValidator->validate("verify", $data);
        $me = $this->authManager->user();
        $verification = $this->verificationModel
            ->where("token", "=", $data["token"])
            ->where("user_id", "=", $me->id)
            ->firstOrFail();
        $me->email_address_verified_at = new DateTime("now", new DateTimeZone("UTC"));
        $me->saveOrFail();
        $verification->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }

    public function resetAction(Request $request): JsonResponse
    {
        $this->authorize("reset", "App\\Models\\Auth");
        $data = $request->json()->all();
        $this->authValidator->validate("reset", $data);
        try {
            $user = $this->userModel
                ->where("email_address", "=", $data["email_address"])
                ->whereNotNull("password")
                ->firstOrFail();
        } catch (Exception $exception) {
            return $this->jsonResponseFactory->success((object) [], 200);
        }
        Mail::to($user->email_address)->send(new ResetPassword($user));
        return $this->jsonResponseFactory->success((object) [], 200);
    }

    public function changeAction(Request $request): JsonResponse
    {
        $this->authorize("change", "App\\Models\\Auth");
        $data = $request->json()->all();
        $this->authValidator->validate("change", $data);
        $passwordReset = $this->passwordResetModel->where("token", "=", $data["token"])->firstOrFail();
        $user = $this->userModel->findOrFail($passwordReset->user_id);
        $user->password = $data["password"];
        $user->saveOrFail();
        $passwordReset->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }

    public function meAction(Request $request): JsonResponse
    {
        $this->authorize("me", "App\\Models\\Auth");
        $me = $this->authManager->user();
        $property = $me->role . "Model";
        $class = "App\\Resources\\" . ucfirst($me->role) . "Resource";
        $resource = new $class($this->{$property}->findOrFail($me->id));
        return $this->jsonResponseFactory->success($resource, 200);
    }
}
