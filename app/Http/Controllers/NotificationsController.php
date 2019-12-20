<?php

namespace App\Http\Controllers;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\JsonResponseFactory;
use App\Models\Notification as NotificationModel;
use App\Resources\NotificationResource;

class NotificationsController extends Controller
{
    private $jsonResponseFactory = null;
    private $notificationModel = null;
    private $authManager = null;

    public function __construct(
        JsonResponseFactory $jsonResponseFactory,
        NotificationModel $notificationModel,
        AuthManager $authManager
    ) {
        $this->jsonResponseFactory = $jsonResponseFactory;
        $this->notificationModel = $notificationModel;
        $this->authManager = $authManager;
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Notification");
        $me = $this->authManager->user();
        $notifications = $this->notificationModel
            ->where("user_id", "=", $me->id)
            ->where("unread", "=", true)
            ->orderBy("created_at", "desc")
            ->get();
        $resources = [];
        foreach ($notifications as $notification) {
            $resources[] = new NotificationResource($notification);
        }
        return $this->jsonResponseFactory->success($resources, 200);
    }

    public function markAction(Request $request, int $id): JsonResponse
    {
        $notification = $this->notificationModel->findOrFail($id);
        $this->authorize("mark", $notification);
        $notification->unread = false;
        $notification->saveOrFail();
        return $this->jsonResponseFactory->success(new NotificationResource($notification), 200);
    }
}
