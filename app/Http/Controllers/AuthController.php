<?php

namespace App\Http\Controllers;

use App\Exceptions\SamePasswordException;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\ConfirmCreateUserRequest;
use App\Http\Requests\ResendByEmailRequest;
use App\Http\Requests\ResetRequest;
use App\Http\Requests\SendLoginDetailsRequest;
use App\Http\Requests\SetPasswordRequest;
use App\Jobs\ResetPasswordJob;
use App\Jobs\SendLoginDetailsJob;
use App\Jobs\UserVerificationJob;
use App\Models\PasswordReset as PasswordResetModel;
use App\Models\V2\Organisations\OrganisationInvite;
use App\Models\V2\Projects\ProjectInvite;
use App\Models\V2\User as UserModel;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function resendByEmail(ResendByEmailRequest $request): JsonResponse
    {
        $user = UserModel::where('email_address', $request->get('email_address'))->first();

        if ($user) {
            UserVerificationJob::dispatch($user, $request->get('callback_url') ? $request->get('callback_url') : null);
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

    public function sendLoginDetailsAction(SendLoginDetailsRequest $request): JsonResponse
    {
        $this->authorize('reset', 'App\\Models\\Auth');
        $data = $request->json()->all();

        try {
            $user = UserModel::where('email_address', '=', $data['email_address'])
                ->whereNull('password')
                ->firstOrFail();
        } catch (Exception $exception) {
            return JsonResponseHelper::success((object) [], 200);
        }

        SendLoginDetailsJob::dispatch($user, isset($data['callback_url']) ? $data['callback_url'] : null);

        return JsonResponseHelper::success((object) [], 200);
    }

    public function getEmailByResetTokenAction(Request $request): JsonResponse
    {
        $data = $request->query();

        $passwordReset = PasswordResetModel::where('token', '=', $data['token'])->first();

        if (! $passwordReset) {
            return JsonResponseHelper::success((object) [
                'email_address' => null,
                'token_used' => true,
            ], 200);
        }
        if (Carbon::parse($passwordReset->created_at)->addDays(7)->isPast()) {
            $passwordReset->delete();

            return JsonResponseHelper::success((object) [
                'email_address' => null,
                'token_used' => true,
            ], 200);
        }

        $user = UserModel::findOrFail($passwordReset->user_id);

        return JsonResponseHelper::success((object) [
            'email_address' => $user->email_address,
            'locale' => $user->locale,
            'token_used' => false,
        ], 200);
    }

    public function setNewPasswordAction(SetPasswordRequest $request): JsonResponse
    {
        $this->authorize('change', 'App\\Models\\Auth');
        $data = $request->json()->all();
        $passwordReset = PasswordResetModel::where('token', '=', $data['token'])->firstOrFail();
        $user = UserModel::findOrFail($passwordReset->user_id);
        if (Hash::check($data['password'], $user->password)) {
            throw new SamePasswordException();
        }
        $user->password = $data['password'];

        if (empty($user->email_address_verified_at)) {
            $user->email_address_verified_at = new DateTime('now', new DateTimeZone('UTC'));
        }

        $user->saveOrFail();
        $passwordReset->delete();

        return JsonResponseHelper::success((object) [], 200);
    }

    public function completeUserSignup(ConfirmCreateUserRequest $request): JsonResponse
    {
        $this->authorize('change', 'App\\Models\\Auth');
        $data = $request->json()->all();
        $passwordReset = PasswordResetModel::where('token', '=', $data['token'])->firstOrFail();
        $user = UserModel::findOrFail($passwordReset->user_id);
        $organisationInvites = OrganisationInvite::where('email_address', $user->email_address)
            ->whereNull('accepted_at')
            ->get();
        if ($organisationInvites->count() > 0) {
            foreach ($organisationInvites as $invite) {
                $invite->email_address = $data['email_address'];
                if ($invite->accepted_at === null) {
                    $invite->accepted_at = now();
                    $invite->saveOrFail();
                }
            }
        } else {
            $projectInvites = ProjectInvite::where('email_address', $user->email_address)->get();
            foreach ($projectInvites as $invite) {
                $invite->email_address = $data['email_address'];
                $user->projects()->sync([$invite->project_id => ['is_monitoring' => true, 'status' => 'active']]);
                if ($invite->accepted_at === null) {
                    $invite->accepted_at = now();
                    $invite->saveOrFail();
                }
            }
        }
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->job_role = $data['job_role'];
        $user->phone_number = $data['phone_number'];
        $user->email_address = $data['email_address'];
        $user->password = $data['password'];
        $user->email_address_verified_at = new DateTime('now', new DateTimeZone('UTC'));
        $role = 'project-developer';
        $user->assignRole($role);
        $user->saveOrFail();
        $passwordReset->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
