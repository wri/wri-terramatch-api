<?php

namespace App\Http\Controllers\V2\User;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\User\AdminUserCreationRequest;
use App\Http\Resources\V2\User\UserResource;
use App\Models\Framework;
use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminUserCreationController extends Controller
{
    /**
     * Create a new user from the admin panel
     */
    public function store(AdminUserCreationRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        try {
            return DB::transaction(function () use ($request) {
                $validatedData = $request->validated();

                $user = new User($validatedData);
                $user->save();

                $user->email_address_verified_at = $user->created_at;
                $user->save();

                $role = $validatedData['role'];
                $user->syncRoles([$role]);

                if (! empty($validatedData['organisation'])) {
                    $organisation = Organisation::isUuid($validatedData['organisation'])->first();
                    if ($organisation) {
                        $organisation->partners()->updateExistingPivot($user, ['status' => 'approved'], false);
                        $user->organisation_id = $organisation->id;
                        $user->save();
                    }
                }

                if (! empty($validatedData['direct_frameworks'])) {
                    $frameworkIds = Framework::whereIn('slug', $validatedData['direct_frameworks'])
                        ->pluck('id')
                        ->toArray();
                    $user->frameworks()->sync($frameworkIds);
                }

                return JsonResponseHelper::success(new UserResource($user), 201);
            });
        } catch (\Exception $e) {
            Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return JsonResponseHelper::error([
                'message' => 'Failed to create user',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
