<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StoreInvasivesRequest;
use App\Models\Invasive;
use App\Models\Site;
use App\Resources\InvasiveResource;
use Illuminate\Http\JsonResponse;

class InvasiveController extends Controller
{
    public function createAction(StoreInvasivesRequest $request, Site $site = null): JsonResponse
    {
        $data = $request->all();
        if (is_null($site)) {
            $site = Site::where('id', $data['site_id'])->firstOrFail();
            unset($data['site_id']);
        }

        $this->authorize('update', $site);

        $extra = [
            'site_id' => $site->id,
        ];

        $invasive = Invasive::create(array_merge($data, $extra));

        return JsonResponseHelper::success((object) new InvasiveResource($invasive), 201);
    }

    public function deleteAction(Invasive $invasive): JsonResponse
    {
        $this->authorize('delete', $invasive);

        $invasive->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
