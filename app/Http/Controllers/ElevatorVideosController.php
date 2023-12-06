<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreElevatorVideosRequest;
use App\Jobs\StartElevatorVideoJob;
use App\Models\ElevatorVideo as ElevatorVideoModel;
use App\Resources\ElevatorVideoResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ElevatorVideosController extends Controller
{
    public function createAction(StoreElevatorVideosRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\ElevatorVideo::class);
        $data = $request->json()->all();
        $me = Auth::user();
        $data['introduction'] = UploadHelper::findByIdAndValidate(
            $data['introduction'],
            UploadHelper::VIDEOS,
            $me->id
        );
        $data['aims'] = UploadHelper::findByIdAndValidate(
            $data['aims'],
            UploadHelper::VIDEOS,
            $me->id
        );
        $data['importance'] = UploadHelper::findByIdAndValidate(
            $data['importance'],
            UploadHelper::VIDEOS,
            $me->id
        );
        UploadHelper::assertUnique($data['introduction'], $data['aims'], $data['importance']);
        $elevatorVideo = new ElevatorVideoModel($data);
        $elevatorVideo->user_id = $me->id;
        $elevatorVideo->saveOrFail();
        $elevatorVideo->refresh();
        StartElevatorVideoJob::dispatch($elevatorVideo);
        $resource = new ElevatorVideoResource($elevatorVideo);

        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(ElevatorVideoModel $elevatorVideo): JsonResponse
    {
        $this->authorize('read', $elevatorVideo);
        $resource = new ElevatorVideoResource($elevatorVideo);

        return JsonResponseHelper::success($resource, 200);
    }
}
