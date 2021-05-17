<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\RequestHelper;
use App\Models\Notification as NotificationModel;
use App\Resources\NotificationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Notification");
        $me = Auth::user();
        $query = NotificationModel::where("user_id", "=", $me->id);
        if (RequestHelper::isAndroid($request) || RequestHelper::isIos($request)) {
            $query = $query->where("hidden_from_app", "!=", true);
        }
        $notifications = $query->orderBy("created_at", "desc")->get();
        $resources = [];
        foreach ($notifications as $notification) {
            $resources[] = new NotificationResource($notification);
        }
        return JsonResponseHelper::success($resources, 200);
    }

    public function markAction(Request $request, int $id): JsonResponse
    {
        $notification = NotificationModel::findOrFail($id);
        $this->authorize("mark", $notification);
        $notification->unread = false;
        $notification->saveOrFail();
        return JsonResponseHelper::success(new NotificationResource($notification), 200);
    }
}
