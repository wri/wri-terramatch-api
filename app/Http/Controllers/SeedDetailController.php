<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StoreBulkSeedsRequest;
use App\Http\Requests\StoreSeedsRequest;
use App\Models\SeedDetail;
use App\Models\Site;
use App\Resources\SeedDetailResource;
use Illuminate\Http\JsonResponse;

class SeedDetailController extends Controller
{
    public function createAction(StoreSeedsRequest $request, Site $site = null): JsonResponse
    {
        $data = $request->all();
        if (is_null($site)) {
            $site = Site::where('id', $data['site_id'])->firstOrFail();
            unset($data['site_id']);
        }

        $this->authorize('update', $site);

        $seedDetail = SeedDetail::create(array_merge($data, ['site_id' => $site->id]));

        return JsonResponseHelper::success((object) new SeedDetailResource($seedDetail), 201);
    }

    public function deleteAction(SeedDetail $seedDetail): JsonResponse
    {
        $this->authorize('delete', $seedDetail);

        $seedDetail->delete();

        return JsonResponseHelper::success((object) [], 200);
    }

    public function createBulkAction(Site $site, StoreBulkSeedsRequest $request): JsonResponse
    {
        $data = $request->json()->all();

        $this->authorize('update', $site);

        $site->seedDetails()->delete();

        foreach ($this->prepBulkList(data_get($data, 'collection', [])) as $name => $seed) {
            $this->addSeed(
                [
                    'name' => $name,
                    'weight_of_sample' => $seed['weight_of_sample'],
                    'seeds_in_sample' => $seed['seeds_in_sample'],
                ],
                $site,
            );
        }

        return JsonResponseHelper::success(['message' => 'success'], 201);
    }

    private function prepBulkList(array $collection): array
    {
        $prepList = [];
        foreach ($collection as $item) {
            $name = $item['name'];
            $weightOfSample = $item['weight_of_sample'];
            $seedsInSample = $item['seeds_in_sample'];

            if (! empty($prepList[$name])) {
                $prepList[$name] = [
                    'weight_of_sample' => $prepList[$name]['weight_of_sample'] + $weightOfSample,
                    'seeds_in_sample' => $prepList[$name]['seeds_in_sample'] + $seedsInSample,
                ];
            } else {
                $prepList[$name] = [
                    'weight_of_sample' => $weightOfSample,
                    'seeds_in_sample' => $seedsInSample,
                ];
            }
        }

        return $prepList;
    }

    private function addSeed(array $data, Site $site): void
    {
        SeedDetail::create(array_merge($data, ['site_id' => $site->id]));
    }
}
