<?php

namespace App\Http\Controllers\V2\ImpactStory;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\ImpactStories\StoreImpactStoryRequest;
use App\Http\Requests\V2\ImpactStories\UpdateImpactStoryRequest;
use App\Http\Resources\V2\ImpactStory\ImpactStoriesCollection;
use App\Http\Resources\V2\ImpactStory\ImpactStoryResource;
use App\Models\V2\ImpactStory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ImpactStoryController extends Controller
{
    public function index(Request $request): ImpactStoriesCollection
    {
        $this->authorize('readAll', ImpactStory::class);
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = ['date', '-date', 'title', '-title', 'created_at', '-created_at'];

        $qry = QueryBuilder::for(ImpactStory::class)
            ->with(['organization'])
            ->allowedFilters([
                AllowedFilter::exact('organization_id'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if ($request->query('search')) {
            $ids = ImpactStory::where('title', 'like', '%' . trim($request->query('search')) . '%')
                ->pluck('id')
                ->toArray();

            if (empty($ids)) {
                return new ImpactStoriesCollection([]);
            }
            $qry->whereIn('id', $ids);
        }

        $collection = $qry->paginate($perPage)->appends(request()->query());

        return new ImpactStoriesCollection($collection);
    }

    public function show(ImpactStory $impactStory, Request $request): ImpactStoryResource
    {
        $this->authorize('read', $impactStory);

        return new ImpactStoryResource($impactStory);
    }

    public function store(StoreImpactStoryRequest $request)
    {
        $this->authorize('create', ImpactStory::class);
        $data = $request->validated();
        $impactStory = ImpactStory::create($data);

        return new ImpactStoryResource($impactStory);
    }

    public function update(ImpactStory $impactStory, UpdateImpactStoryRequest $request)
    {
        $this->authorize('update', $impactStory);
        $data = $request->validated();
        $impactStory->update($data);

        return new ImpactStoryResource($impactStory);
    }

    public function destroy(ImpactStory $impactStory, Request $request): JsonResponse
    {
        $this->authorize('delete', $impactStory);
        $impactStory->delete();

        return JsonResponseHelper::success(['Impact Story has been deleted.'], 200);
    }
}
