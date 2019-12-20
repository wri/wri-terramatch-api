<?php

namespace App\Http\Controllers;

use App\Http\JsonResponseFactory;
use App\Mail\UserInvited;
use App\Mail\UserVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User as UserModel;
use App\Validators\UserValidator;
use Illuminate\Auth\AuthManager;
use App\Services\FileService;
use App\Models\Upload as UploadModel;
use App\Models\Organisation as OrganisationModel;
use App\Resources\UserResource;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    protected $jsonResponseFactory = null;
    protected $userModel = null;
    protected $userValidator = null;
    protected $authManager = null;
    protected $fileService = null;
    protected $uploadModel = null;
    protected $organisationModel = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        UserModel $userModel,
        UserValidator $userValidator,
        AuthManager $authManager,
        FileService $fileService,
        UploadModel $uploadModel,
        OrganisationModel $organisationModel
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->userModel = $userModel;
        $this->userValidator = $userValidator;
        $this->authManager = $authManager;
        $this->fileService = $fileService;
        $this->uploadModel = $uploadModel;
        $this->organisationModel = $organisationModel;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\User");
        $data = $request->json()->all();
        $this->userValidator->validate("create", $data);
        $data["role"] = "user";
        $user = $this->userModel->newInstance($data);
        $user->saveOrFail();
        $user->refresh();
        Mail::to($user->email_address)->send(new UserVerification($user));
        return $this->jsonResponseFactory->success(new UserResource($user), 201);
    }

    public function inviteAction(Request $request): JsonResponse
    {
        $this->authorize("invite", "App\\Models\\User");
        $data = $request->json()->all();
        $this->userValidator->validate("invite", $data);
        $me = $this->authManager->user();
        $data["organisation_id"] = $me->organisation_id;
        $data["role"] = "user";
        $user = $this->userModel->newInstance($data);
        $user->saveOrFail();
        $user->refresh();
        Mail::to($user->email_address)->send(new UserInvited($user));
        return $this->jsonResponseFactory->success(new UserResource($user), 201);
    }

    public function acceptAction(Request $request): JsonResponse
    {
        $this->authorize("accept", "App\\Models\\User");
        $data = $request->json()->all();
        $this->userValidator->validate("accept", $data);
        $user = $this->userModel
            ->user()
            ->invited()
            ->where("email_address", "=", $data["email_address"])
            ->firstOrFail();
        $user->fill($data);
        $user->saveOrFail();
        Mail::to($user->email_address)->send(new UserVerification($user));
        return $this->jsonResponseFactory->success(new UserResource($user), 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $user = $this->userModel
            ->user()
            ->where("id", "=", $id)
            ->firstOrFail();
        $this->authorize("read", $user);
        return $this->jsonResponseFactory->success(new UserResource($user), 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $user = $this->userModel
            ->user()
            ->where("id", "=", $id)
            ->firstOrFail();
        $this->authorize("update", $user);
        $data = $request->json()->all();
        $this->userValidator->validate("update", $data);
        $me = $this->authManager->user();
        $changed = array_key_exists("email_address", $data) && $data["email_address"] != $user->email_address;
        if (array_key_exists("avatar", $data)) {
            $data["avatar"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
                $data["avatar"], UploadModel::IMAGES, $me->id
            );
        }
        $user->fill($data);
        $user->saveOrFail();
        if ($changed) {
            Mail::to($user->email_address)->send(new UserVerification($user));
            $user->email_address_verified_at = null;
        }
        return $this->jsonResponseFactory->success(new UserResource($user), 200);
    }

    public function readAllByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $organisation = $this->organisationModel->findOrFail($id);
        $this->authorize("read", $organisation);
        $users = $this->userModel
            ->user()
            ->where("organisation_id", "=", $organisation->id)
            ->get();
        $resources = [];
        foreach ($users as $user) {
            $resources[] = new UserResource($user);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }
}
