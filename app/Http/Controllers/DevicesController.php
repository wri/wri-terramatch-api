<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StoreDeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Device as DeviceModel;
use App\Resources\DeviceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class DevicesController extends Controller
{
    public function createAction(StoreDeviceRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Device::class);
        $data = $request->json()->all();
        $me = Auth::user();
        $pushService = App::make(\App\Services\PushService::class);

        $extra = [
            'user_id' => $me->id,
            'arn' => $pushService->fetchEndpointArn($data['os'], $data['push_token']),
        ];
        $device = DeviceModel::create(array_merge($data, $extra));

        return JsonResponseHelper::success((object) new DeviceResource($device), 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $device = DeviceModel::findOrFail($id);
        $this->authorize('read', $device);

        return JsonResponseHelper::success(new DeviceResource($device), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', \App\Models\Device::class);
        $me = Auth::user();
        $devices = DeviceModel::where('user_id', '=', $me->id)->orderByDesc('created_at')->get();
        $resources = [];
        foreach ($devices as $device) {
            $resources[] = new DeviceResource($device);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function updateAction(UpdateDeviceRequest $request, int $id): JsonResponse
    {
        $device = DeviceModel::findOrFail($id);
        $this->authorize('update', $device);
        $data = $request->json()->all();
        $changed = array_key_exists('push_token', $data) && $data['push_token'] != $device->push_token;
        $device->fill($data);
        if ($changed) {
            $pushService = App::make(\App\Services\PushService::class);
            $device->arn = $pushService->fetchEndpointArn($device->os, $device->push_token);
        }
        $device->saveOrFail();

        return JsonResponseHelper::success(new DeviceResource($device), 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $device = DeviceModel::findOrFail($id);
        $this->authorize('delete', $device);
        $device->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
