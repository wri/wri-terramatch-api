<?php

namespace App\Http\Controllers;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use App\Validators\DeviceValidator;
use App\Models\Device as DeviceModel;
use App\Services\PushService;
use App\Resources\DeviceResource;

class DevicesController extends Controller
{
    private $jsonResponseFactory = null;
    private $deviceValidator = null;
    private $deviceModel = null;
    private $authManager = null;
    private $pushService = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        DeviceValidator $deviceValidator,
        DeviceModel $deviceModel,
        AuthManager $authManager,
        PushService $pushService
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->deviceValidator = $deviceValidator;
        $this->deviceModel = $deviceModel;
        $this->authManager = $authManager;
        $this->pushService = $pushService;
    }

    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\Device");
        $data = $request->json()->all();
        $this->deviceValidator->validate("create", $data);
        $me = $this->authManager->user();
        $device = $this->deviceModel->newInstance($data);
        $device->user_id = $me->id;
        $device->arn = $this->pushService->fetchEndpointArn($device->os, $device->push_token);
        $device->saveOrFail();
        $device->refresh();
        return $this->jsonResponseFactory->success((object) new DeviceResource($device), 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $device = $this->deviceModel->findOrFail($id);
        $this->authorize("read", $device);
        return $this->jsonResponseFactory->success(new DeviceResource($device), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Device");
        $me = $this->authManager->user();
        $devices = $this->deviceModel->where("user_id", "=", $me->id)->get();
        $resources = [];
        foreach ($devices as $device) {
            $resources[] = new DeviceResource($device);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $device = $this->deviceModel->findOrFail($id);
        $this->authorize("update", $device);
        $data = $request->json()->all();
        $this->deviceValidator->validate("update", $data);
        $changed = array_key_exists("push_token", $data) && $data["push_token"] != $device->push_token;
        $device->fill($data);
        if ($changed) {
            $device->arn = $this->pushService->fetchEndpointArn($device->os, $device->push_token);
        }
        $device->saveOrFail();
        return $this->jsonResponseFactory->success(new DeviceResource($device), 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $device = $this->deviceModel->findOrFail($id);
        $this->authorize("delete", $device);
        $device->delete();
        return $this->jsonResponseFactory->success((object) [], 200);
    }
}
