<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\StoreTerrafundNurseryRequest;
use App\Http\Requests\Terrafund\StoreTerrafundTreeSpeciesRequest;
use App\Http\Requests\Terrafund\UpdateTerrafundNurseryRequest;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundTreeSpecies;
use App\Resources\Terrafund\TerrafundNurseryResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class TerrafundNurseryController extends Controller
{
    public function createAction(StoreTerrafundNurseryRequest $request): JsonResponse
    {
        $this->authorize('create', TerrafundNursery::class);
        $data = $request->json()->all();
        if (! Auth::user()->terrafundProgrammes->contains($data['terrafund_programme_id'])) {
            throw new AuthorizationException();
        }

        $nursery = TerrafundNursery::create($data);

        return JsonResponseHelper::success(new TerrafundNurseryResource($nursery), 201);
    }

    public function updateAction(UpdateTerrafundNurseryRequest $request, TerrafundNursery $terrafundNursery): JsonResponse
    {
        $data = $request->all();
        $this->authorize('update', $terrafundNursery);

        if (! empty(data_get($data, 'tree_species'))) {
            TerrafundTreeSpecies::where('treeable_type', TerrafundNursery::class)
                ->where('treeable_id', data_get($terrafundNursery, 'id'))
                ->delete();

            foreach (data_get($data, 'tree_species') as $treeSpeciesData) {
                $payload = [
                    'treeable_type' => 'nursery',
                    'treeable_id' => data_get($terrafundNursery, 'id'),
                    'name' => data_get($treeSpeciesData, 'name'),
                    'amount' => data_get($treeSpeciesData, 'amount'),
                ];
                $controller = new TerrafundTreeSpeciesController();
                $controller->callAction('createAction', [new StoreTerrafundTreeSpeciesRequest($payload)]);
            }
        }

        $terrafundNursery->fill(Arr::except($data, ['tree_species', 'additional_files']));
        $terrafundNursery->saveOrFail();

        return JsonResponseHelper::success(new TerrafundNurseryResource($terrafundNursery), 200);
    }

    public function readAction(TerrafundNursery $terrafundNursery): JsonResponse
    {
        $this->authorize('read', $terrafundNursery);

        return JsonResponseHelper::success(new TerrafundNurseryResource($terrafundNursery), 200);
    }

    public function readMyNurseriesAction(Request $request): JsonResponse
    {
        $this->authorize('readMy', TerrafundNursery::class);

        $nurseries = TerrafundNursery::whereIn('terrafund_programme_id', Auth::user()->terrafundProgrammes->pluck('id'))->get();

        $resources = [];
        foreach ($nurseries as $nursery) {
            $resources[] = new TerrafundNurseryResource($nursery);
        }

        return JsonResponseHelper::success($resources, 200);
    }
}
