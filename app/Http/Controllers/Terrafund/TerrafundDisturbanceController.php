<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\StoreTerrafundDisturbanceRequest;
use App\Http\Requests\Terrafund\UpdateTerrafundDisturbanceRequest;
use App\Models\Terrafund\TerrafundDisturbance;
use App\Resources\Terrafund\TerrafundDisturbanceResource;
use Illuminate\Http\JsonResponse;

class TerrafundDisturbanceController extends Controller
{
    public function createAction(StoreTerrafundDisturbanceRequest $request): JsonResponse
    {
        $data = $request->json()->all();

        $disturbanceable = getTerrafundModelDataFromMorphable($data['disturbanceable_type'], $data['disturbanceable_id']);

        $this->authorize('createDisturbance', $disturbanceable['model']);

        $extra = [
            'disturbanceable_type' => get_class($disturbanceable['model']),
        ];
        $disturbance = TerrafundDisturbance::create(array_merge($data, $extra));

        return JsonResponseHelper::success(new TerrafundDisturbanceResource($disturbance), 201);
    }

    public function updateAction(UpdateTerrafundDisturbanceRequest $request, TerrafundDisturbance $terrafundDisturbance): JsonResponse
    {
        $data = $request->json()->all();
        $this->authorize('updateDisturbance', $terrafundDisturbance->disturbanceable);

        $terrafundDisturbance->update($data);

        return JsonResponseHelper::success(new TerrafundDisturbanceResource($terrafundDisturbance), 200);
    }

    public function deleteAction(TerrafundDisturbance $terrafundDisturbance): JsonResponse
    {
        $this->authorize('deleteDisturbance', $terrafundDisturbance->disturbanceable);

        $terrafundDisturbance->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
