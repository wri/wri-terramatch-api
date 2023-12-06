<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\StoreBulkTerrafundTreeSpeciesRequest;
use App\Http\Requests\Terrafund\StoreTerrafundTreeSpeciesRequest;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundTreeSpecies;
use App\Resources\Terrafund\TerrafundTreeSpeciesResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TerrafundTreeSpeciesController extends Controller
{
    public function createAction(StoreTerrafundTreeSpeciesRequest $request): JsonResponse
    {
        $data = $request->all();

        $treeable = getTerrafundModelDataFromMorphable($data['treeable_type'], $data['treeable_id']);

        $this->authorize('createTree', $treeable['model']);

        $treeSpecies = $this->createTree($data, $treeable);

        return JsonResponseHelper::success(new TerrafundTreeSpeciesResource($treeSpecies), 201);
    }

    public function createBulkAction(StoreBulkTerrafundTreeSpeciesRequest $request): JsonResponse
    {
        $data = $request->json()->all();

        $treeable = getTerrafundModelDataFromMorphable($data['treeable_type'], $data['treeable_id']);

        $this->authorize('createTree', $treeable['model']);

        TerrafundTreeSpecies::where('treeable_type', get_class($treeable['model']))
            ->where('treeable_id', $data['treeable_id'])
            ->delete();

        foreach ($this->prepBulkList(data_get($data, 'collection', [])) as $name => $amount) {
            $this->createTree(
                [
                'name' => $name,
                'amount' => $amount,
                ],
                $treeable
            );
        }

        return JsonResponseHelper::success(['message' => 'success'], 201);
    }

    public function deleteAction(TerrafundTreeSpecies $treeSpecies): JsonResponse
    {
        $this->authorize('deleteTree', $treeSpecies->treeable);

        $treeSpecies->delete();

        return JsonResponseHelper::success([], 200);
    }

    private function createTree(array $data, array $treeable)
    {
        $extra = [
            'treeable_type' => get_class($treeable['model']),
            'treeable_id' => $treeable['model']->id,
        ];

        $treeSpecies = TerrafundTreeSpecies::create(array_merge($data, $extra));

        return $treeSpecies;
    }

    public function readAllSiteTreesAction(Request $request): JsonResponse
    {
        $this->authorize('readAllTreeSpecies', TerrafundSite::class);

        $terrafundSites = TerrafundSite::whereIn('terrafund_programme_id', Auth::user()->terrafundProgrammes->pluck('id'))->get();

        $resources = [];
        foreach ($terrafundSites as $terrafundSite) {
            foreach ($terrafundSite->terrafundTreeSpecies->unique('name') as $siteTreeSpecies) {
                $resources[] = new TerrafundTreeSpeciesResource($siteTreeSpecies);
            }
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllNurseryTreesAction(Request $request): JsonResponse
    {
        $this->authorize('readAllTreeSpecies', TerrafundNursery::class);

        $terrafundNurseries = TerrafundNursery::whereIn('terrafund_programme_id', Auth::user()->terrafundProgrammes->pluck('id'))->get();

        $resources = [];
        foreach ($terrafundNurseries as $terrafundNursery) {
            foreach ($terrafundNursery->terrafundTreeSpecies->unique('name') as $siteTreeSpecies) {
                $resources[] = new TerrafundTreeSpeciesResource($siteTreeSpecies);
            }
        }

        return JsonResponseHelper::success($resources, 200);
    }

    private function prepBulkList(array $collection): array
    {
        $prepList = [];
        foreach ($collection as $item) {
            $name = $item['name'];
            $amount = $item['amount'];
            if (! empty($name) && ! empty($amount)) {
                $prepList[$name] = empty($prepList[$name]) ? $amount : $prepList[$name] + $amount;
            }
        }

        return $prepList;
    }
}
