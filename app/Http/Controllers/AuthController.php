<?php

namespace App\Http\Controllers;

use App\Exceptions\FailedLoginException;
use App\Exceptions\SamePasswordException;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\ResendByEmailRequest;
use App\Http\Requests\ResendRequest;
use App\Http\Requests\ResetRequest;
use App\Http\Requests\VerifyRequest;
use App\Http\Resources\V2\User\MeResource;
use App\Jobs\ResetPasswordJob;
use App\Jobs\UserVerificationJob;
use App\Models\PasswordReset as PasswordResetModel;
use App\Models\User as UserModel;
use App\Models\Verification as VerificationModel;
use App\Models\V2\Projects\ProjectInvite;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginAction(LoginRequest $request): JsonResponse
    {
        $this->authorize('login', 'App\\Models\\Auth');
        $data = $request->json()->all();
        $token = Auth::attempt($data);
        if (! $token) {
            throw new FailedLoginException();
        }
        $me = Auth::user();
        if (is_null($me->password)) {
            throw new FailedLoginException();
        }
        $me->last_logged_in_at = new DateTime('now', new DateTimeZone('UTC'));
        $me->saveOrFail();

        return JsonResponseHelper::success((object) ['token' => $token], 200);
    }

    public function logoutAction(Request $request): JsonResponse
    {
        $this->authorize('logout', 'App\\Models\\Auth');
        Auth::logout();

        return JsonResponseHelper::success((object) [], 200);
    }

    public function refreshAction(Request $request): JsonResponse
    {
        $this->authorize('refresh', 'App\\Models\\Auth');
        $token = Auth::refresh();

        return JsonResponseHelper::success((object) ['token' => $token], 200);
    }

    public function resendAction(ResendRequest $request): JsonResponse
    {
        $this->authorize('resend', 'App\\Models\\Auth');
        $me = Auth::user();
        UserVerificationJob::dispatch($me, $request->get('callback_url') ? $request->get('callback_url') : null);

        return JsonResponseHelper::success((object) [], 200);
    }

    public function resendByEmail(ResendByEmailRequest $request): JsonResponse
    {
        $user = UserModel::where('email_address', $request->get('email_address'))->first();

        if ($user) {
            UserVerificationJob::dispatch($user, $request->get('callback_url') ? $request->get('callback_url') : null);
        }

        return JsonResponseHelper::success((object) [], 200);
    }

    public function verifyAction(VerifyRequest $request): JsonResponse
    {
        $this->authorize('verify', 'App\\Models\\Auth');
        $data = $request->json()->all();
        $me = Auth::user();
        $verification = VerificationModel::where('token', '=', $data['token'])
            ->where('user_id', '=', $me->id)
            ->firstOrFail();

        $me->email_address_verified_at = new DateTime('now', new DateTimeZone('UTC'));
        $me->saveOrFail();
        $verification->delete();

        return JsonResponseHelper::success((object) [], 200);
    }

    public function verifyUnauthorizedAction(VerifyRequest $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $data = $request->json()->all();
        $verification = VerificationModel::where('token', '=', $data['token'])
            ->firstOrFail();
        $user = UserModel::where('id', $verification->user_id)->firstOrFail();
        $user->email_address_verified_at = new DateTime('now', new DateTimeZone('UTC'));
        $user->saveOrFail();
        $verification->delete();

        $invites = ProjectInvite::where('email_address', $user->email_address)->orderBy('created_at', 'desc')->get();
        $is_first = true;
        foreach ($invites as $invite) {
            $invite = ProjectInvite::where('token', $invite->token)
                ->where('email_address', $user->email_address)
                ->first();
            if ($is_first) {
                $user->organisation_id = $invite->project->organisation_id;
                $user->saveOrFail();
                $is_first = false;
            }
            $user->projects()->sync([$invite->project_id => ['is_monitoring' => true]]);
            if ($invite->accepted_at === null) {
                $invite->accepted_at = now();
                $invite->saveOrFail();
            }
        }
        return JsonResponseHelper::success((object) [], 200);
    }

    public function resetAction(ResetRequest $request): JsonResponse
    {
        $this->authorize('reset', 'App\\Models\\Auth');
        $data = $request->json()->all();

        try {
            $user = UserModel::where('email_address', '=', $data['email_address'])
                ->whereNotNull('password')
                ->firstOrFail();
        } catch (Exception $exception) {
            return JsonResponseHelper::success((object) [], 200);
        }
        ResetPasswordJob::dispatch($user, isset($data['callback_url']) ? $data['callback_url'] : null);

        return JsonResponseHelper::success((object) [], 200);
    }

    public function changeAction(ChangePasswordRequest $request): JsonResponse
    {
        $this->authorize('change', 'App\\Models\\Auth');
        $data = $request->json()->all();
        $passwordReset = PasswordResetModel::where('token', '=', $data['token'])->firstOrFail();
        $user = UserModel::findOrFail($passwordReset->user_id);
        if (Hash::check($data['password'], $user->password)) {
            throw new SamePasswordException();
        }
        $user->password = $data['password'];
        $user->saveOrFail();
        $passwordReset->delete();

        return JsonResponseHelper::success((object) [], 200);
    }

    public function meAction(Request $request): MeResource
    {
        $this->authorize('me', 'App\\Models\\Auth');
        $me = Auth::user();
        /*
        $role = $me->role;
        if ($me->role == 'terrafund_admin') {
            $role = 'user';
        }
        $classes = [
            'App\\Models\\' . ucfirst($role),
            'App\\Resources\\' . ucfirst($role) . 'Resource',
        ];
        $model = $classes[0]::findOrFail($me->id);
        $resource = new $classes[1]($model);

        return JsonResponseHelper::success($resource, 200);
        */

        return new MeResource($me);
    }

    public function deleteMeAction(Request $request): JsonResponse
    {
        if (Auth::guest()) {
            return JsonResponseHelper::error([], 401);
        }

        $me = Auth::user();

        $this->authorize('deleteSelf', $me);

        $me->wipeData();
        $me->save();
        $me->delete();
        Auth::logout();

        return JsonResponseHelper::success((object) ['message' => 'user successfully deleted.'], 200);
    }
}
