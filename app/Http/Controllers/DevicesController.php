<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\Device as DeviceModel;
use App\Resources\DeviceResource;
use App\Validators\DeviceValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class DevicesController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\Device");
        $data = $request->json()->all();
        DeviceValidator::validate("CREATE", $data);
        $me = Auth::user();
        $pushService = App::make("App\\Services\\PushService");
        $device = new DeviceModel($data);
        $device->user_id = $me->id;
        $device->arn = $pushService->fetchEndpointArn($device->os, $device->push_token);
        $device->saveOrFail();
        $device->refresh();
        return JsonResponseHelper::success((object) new DeviceResource($device), 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $device = DeviceModel::findOrFail($id);
        $this->authorize("read", $device);
        return JsonResponseHelper::success(new DeviceResource($device), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Device");
        $me = Auth::user();
        $devices = DeviceModel::where("user_id", "=", $me->id)->orderBy("created_at", "desc")->get();
        $resources = [];
        foreach ($devices as $device) {
            $resources[] = new DeviceResource($device);
        }
        return JsonResponseHelper::success($resources, 200);
    }

    public function updateAction(Request $request, int $id): JsonResponse
    {
        $device = DeviceModel::findOrFail($id);
        $this->authorize("update", $device);
        $data = $request->json()->all();
        DeviceValidator::validate("UPDATE", $data);
        $changed = array_key_exists("push_token", $data) && $data["push_token"] != $device->push_token;
        $device->fill($data);
        if ($changed) {
            $pushService = App::make("App\\Services\\PushService");
            $device->arn = $pushService->fetchEndpointArn($device->os, $device->push_token);
        }
        $device->saveOrFail();
        return JsonResponseHelper::success(new DeviceResource($device), 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $device = DeviceModel::findOrFail($id);
        $this->authorize("delete", $device);
        $device->delete();
        return JsonResponseHelper::success((object) [], 200);
    }
}
