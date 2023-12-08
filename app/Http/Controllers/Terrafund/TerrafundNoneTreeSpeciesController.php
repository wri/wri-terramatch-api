<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\StoreBulkNonTerrafundTreeSpeciesRequest;
use App\Http\Requests\Terrafund\StoreTerrafundNonTreeSpeciesRequest;
use App\Models\Terrafund\TerrafundNoneTreeSpecies;
use App\Resources\Terrafund\TerrafundNoneTreeSpeciesResource;
use Illuminate\Http\JsonResponse;

class TerrafundNoneTreeSpeciesController extends Controller
{
    public function createAction(StoreTerrafundNonTreeSpeciesRequest $request): JsonResponse
    {
        $data = $request->json()->all();

        $speciesable = getTerrafundModelDataFromMorphable($data['speciesable_type'], $data['speciesable_id']);

        $this->authorize('createNoneTree', $speciesable['model']);

        $noneTreeSpecies = $this->createNoneTree($data, $speciesable);

        return JsonResponseHelper::success(new TerrafundNoneTreeSpeciesResource($noneTreeSpecies), 201);
    }

    public function createBulkAction(StoreBulkNonTerrafundTreeSpeciesRequest $request): JsonResponse
    {
        $data = $request->json()->all();

        $speciesable = getTerrafundModelDataFromMorphable($data['speciesable_type'], $data['speciesable_id']);

        $this->authorize('createTree', $speciesable['model']);

        TerrafundNoneTreeSpecies::where('speciesable_type', get_class($speciesable['model']))
            ->where('speciesable_id', $data['speciesable_id'])
            ->delete();

        foreach ($this->prepBulkList(data_get($data, 'collection', [])) as $name => $amount) {
            $this->createNoneTree(
                [
                'name' => $name,
                'amount' => $amount,
                ],
                $speciesable
            );
        }

        return JsonResponseHelper::success(['message' => 'success'], 201);
    }

    public function deleteAction(TerrafundNoneTreeSpecies $noneTreeSpecies): JsonResponse
    {
        $this->authorize('deleteNoneTree', $noneTreeSpecies->speciesable);

        $noneTreeSpecies->delete();

        return JsonResponseHelper::success([], 200);
    }

    private function createNoneTree(array $data, array $speciesable)
    {
        $extra = [
            'speciesable_type' => get_class($speciesable['model']),
            'speciesable_id' => $speciesable['model']->id,
        ];

        $noneTreeSpecies = TerrafundNoneTreeSpecies::create(array_merge($data, $extra));

        return $noneTreeSpecies;
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
