<?php

namespace App\Http\Controllers;

use App\Exceptions\ProgrammeHasNoAimsException;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\UpdateAimsRequest;
use App\Models\Programme;
use App\Resources\AimResource;
use Illuminate\Http\JsonResponse;

class AimController extends Controller
{
    public function readAction(Programme $programme): JsonResponse
    {
        $this->authorize('read', $programme);

        if (! $programme->aim()->exists()) {
            throw new ProgrammeHasNoAimsException();
        }

        return JsonResponseHelper::success((object) new AimResource($programme->aim), 200);
    }

    public function updateAction(UpdateAimsRequest $request, Programme $programme = null): JsonResponse
    {
        $data = $request->json()->all();
        if (is_null($programme)) {
            $programme = Programme::where('id', $data['programme_id'])->firstOrFail();
            unset($data['programme_id']);
        }

        $this->authorize('update', $programme);

        $programme->aim()->updateOrCreate(
            ['programme_id' => $programme->id],
            $data
        );

        return JsonResponseHelper::success((object) new AimResource($programme->aim), 201);
    }
}
