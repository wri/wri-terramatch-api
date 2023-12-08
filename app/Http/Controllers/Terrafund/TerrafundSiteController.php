<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\StoreTerrafundSiteRequest;
use App\Http\Requests\Terrafund\UpdateTerrafundSiteRequest;
use App\Models\Terrafund\TerrafundSite;
use App\Resources\Terrafund\TerrafundSiteResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TerrafundSiteController extends Controller
{
    public function createAction(StoreTerrafundSiteRequest $request): JsonResponse
    {
        $this->authorize('create', TerrafundSite::class);
        $data = $request->json()->all();
        if (! Auth::user()->terrafundProgrammes->contains($data['terrafund_programme_id'])) {
            throw new AuthorizationException();
        }

        $site = TerrafundSite::create($data);

        return JsonResponseHelper::success(new TerrafundSiteResource($site), 201);
    }

    public function updateAction(UpdateTerrafundSiteRequest $request, TerrafundSite $terrafundSite): JsonResponse
    {
        $data = $request->all();
        $this->authorize('update', $terrafundSite);

        $terrafundSite->update($data);

        return JsonResponseHelper::success(new TerrafundSiteResource($terrafundSite), 200);
    }

    public function readAction(TerrafundSite $terrafundSite): JsonResponse
    {
        $this->authorize('read', $terrafundSite);

        return JsonResponseHelper::success(new TerrafundSiteResource($terrafundSite), 200);
    }

    public function readMySitesAction(Request $request): JsonResponse
    {
        $this->authorize('readMy', TerrafundSite::class);

        $sites = TerrafundSite::whereIn('terrafund_programme_id', Auth::user()->terrafundProgrammes->pluck('id'))->get();

        $resources = [];
        foreach ($sites as $site) {
            $resources[] = new TerrafundSiteResource($site);
        }

        return JsonResponseHelper::success($resources, 200);
    }
}
