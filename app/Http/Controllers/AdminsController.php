<?php

namespace App\Http\Controllers;

use App\Http\JsonResponseFactory;
use App\Mail\UserInvited;
use App\Mail\UserVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Admin as AdminModel;
use App\Validators\AdminValidator;
use Illuminate\Auth\AuthManager;
use App\Services\FileService;
use App\Models\Upload as UploadModel;
use App\Resources\AdminResource;
use Illuminate\Support\Facades\Mail;

class AdminsController extends Controller
{
    private $jsonResponseFactory = null;
    private $adminModel = null;
    private $adminValidator = null;
    private $authManager = null;
    private $fileService = null;
    private $uploadModel = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        AdminModel $adminModel,
        AdminValidator $adminValidator,
        AuthManager $authManager,
        FileService $fileService,
        UploadModel $uploadModel
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->adminModel = $adminModel;
        $this->adminValidator = $adminValidator;
        $this->authManager = $authManager;
        $this->fileService = $fileService;
        $this->uploadModel = $uploadModel;
    }
    public function inviteAction(Request $request): JsonResponse
    {
        $this->authorize("invite", "App\\Models\\Admin");
        $data = $request->json()->all();
        $this->adminValidator->validate("invite", $data);
        $data["role"] = "admin";
        $admin = $this->adminModel->newInstance($data);
        $admin->saveOrFail();
        $admin->refresh();
        Mail::to($admin->email_address)->send(new UserInvited($admin));
        return $this->jsonResponseFactory->success(new AdminResource($admin), 201);
    }

    public function acceptAction(Request $request): JsonResponse
    {
        $this->authorize("accept", "App\\Models\\Admin");
        $data = $request->json()->all();
        $this->adminValidator->validate("accept", $data);
        $admin = $this->adminModel
            ->admin()
            ->invited()
            ->where("email_address", "=", $data["email_address"])
            ->firstOrFail();
        $admin->fill($data);
        $admin->saveOrFail();
        Mail::to($admin->email_address)->send(new UserVerification($admin));
        return $this->jsonResponseFactory->success(new AdminResource($admin), 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $admin = $this->adminModel
            ->admin()
            ->where("id", "=", $id)
            ->firstOrFail();
        $this->authorize("read", $admin);
        return $this->jsonResponseFactory->success(new AdminResource($admin), 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $admin = $this->adminModel
            ->admin()
            ->where("id", "=", $id)
            ->firstOrFail();
        $this->authorize("update", $admin);
        $data = $request->json()->all();
        $this->adminValidator->validate("update", $data);
        $me = $this->authManager->user();
        $changed = array_key_exists("email_address", $data) && $data["email_address"] != $admin->email_address;
        if (array_key_exists("avatar", $data)) {
            $data["avatar"] = $this->uploadModel->findByIdAndExtensionAndUserIdOrFail(
                $data["avatar"], UploadModel::IMAGES, $me->id
            );
        }
        $admin->fill($data);
        $admin->saveOrFail();
        if ($changed) {
            Mail::to($admin->email_address)->send(new UserVerification($admin));
            $admin->email_address_verified_at = null;
        }
        return $this->jsonResponseFactory->success(new AdminResource($admin), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Admin");
        $admins = $this->adminModel
            ->admin()
            ->get();
        $resources = [];
        foreach ($admins as $admin) {
            $resources[] = new AdminResource($admin);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }
}
