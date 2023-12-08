<?php

namespace App\Http\Controllers;

use App\Exceptions\MonitoringExistsException;
use App\Helpers\ArrayHelper;
use App\Helpers\JsonResponseHelper;
use App\Helpers\MonitoringHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\MostRecentActionPitchRequest;
use App\Http\Requests\StorePitchRequest;
use App\Http\Requests\UpdatePitchRequest;
use App\Http\Requests\UpdatePitchVisibilityRequest;
use App\Jobs\CreateFilterRecordJob;
use App\Jobs\NotifyVersionCreatedJob;
use App\Models\Organisation as OrganisationModel;
use App\Models\Pitch as PitchModel;
use App\Models\PitchVersion as PitchVersionModel;
use App\Resources\PitchResource;
use App\Resources\PitchVersionResource;
use App\Services\Version\VersionService;
use DateTime;
use DateTimeZone;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PitchesController extends Controller
{
    protected $versionService = null;

    public function __construct(
        PitchModel $pitchModel,
        PitchVersionModel $pitchVersionModel
    ) {
        $this->versionService = new VersionService($pitchModel, $pitchVersionModel);
    }

    public function createAction(StorePitchRequest $request): JsonResponse
    {
        $this->authorize('create', \App\Models\Pitch::class);
        $childData = $request->json()->all();

        $me = Auth::user();
        $parentData = [
            'organisation_id' => $me->organisation_id,
            'visibility_updated_at' => new DateTime('now', new DateTimeZone('UTC')),
        ];
        $childData['cover_photo'] = UploadHelper::findByIdAndValidate(
            $childData['cover_photo'],
            UploadHelper::IMAGES,
            $me->id
        );
        $childData['video'] = UploadHelper::findByIdAndValidate(
            $childData['video'],
            UploadHelper::VIDEOS,
            $me->id
        );
        UploadHelper::assertUnique($childData['cover_photo'], $childData['video']);
        $parentAndChild = $this->versionService->createParentAndChild($parentData, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new PitchVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize('read', $parentAndChild->parent);
        $resource = new PitchResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function updateAction(UpdatePitchRequest $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize('update', $parentAndChild->parent);
        $childData = $request->json()->all();

        $me = Auth::user();
        if (array_key_exists('cover_photo', $childData)) {
            $childData['cover_photo'] = UploadHelper::findByIdAndValidate(
                $childData['cover_photo'],
                UploadHelper::IMAGES,
                $me->id
            );
        }
        if (array_key_exists('video', $childData)) {
            $childData['video'] = UploadHelper::findByIdAndValidate(
                $childData['video'],
                UploadHelper::VIDEOS,
                $me->id
            );
        }
        UploadHelper::assertUnique(@$childData['cover_photo'], @$childData['video']);
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $childData);
        NotifyVersionCreatedJob::dispatch($parentAndChild->child);
        $resource = new PitchVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllByOrganisationAction(OrganisationModel $organisation): JsonResponse
    {
        $this->authorize('read', $organisation);
        $parentsAndChildren = $this->versionService->findAllParents(function ($query) use ($organisation) {
            $query->where('organisation_id', '=', $organisation->id)
                ->whereNotIn('visibility', ['archived', 'finished']);
        });
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new PitchResource($parentAndChild->parent, $parentAndChild->child);
        }
        $resources = ArrayHelper::sortBy($resources, 'name', ArrayHelper::ASC);

        return JsonResponseHelper::success($resources, 200);
    }

    public function searchAction(Request $request): JsonResponse
    {
        $searchService = App::make(\App\Services\Search\SearchService::class);
        $this->authorize('search', \App\Models\Pitch::class);
        $conditions = $searchService->parse($request);
        $parentsAndChildren = $this->versionService->searchAllApprovedChildren($conditions, Auth::user()->organisation_id);
        CreateFilterRecordJob::dispatch(Auth::user(), 'pitches', $conditions);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new PitchResource($parentAndChild->parent, $parentAndChild->child, true);
        }
        $meta = $searchService->summarise('Pitch', $resources, $conditions);

        return JsonResponseHelper::success($resources, 200, $meta);
    }

    public function inspectByOrganisationAction(OrganisationModel $organisation): JsonResponse
    {
        $this->authorize('inspect', $organisation);
        $parentsAndChildren = $this->versionService->groupAllChildren([['organisation_id', '=', $organisation->id]]);
        $resources = [];
        foreach ($parentsAndChildren as $parentAndChild) {
            $resources[] = new PitchVersionResource($parentAndChild->parent, $parentAndChild->child);
        }
        ArrayHelper::sortDataBy($resources, 'created_at', ArrayHelper::DESC);

        return JsonResponseHelper::success($resources, 200);
    }

    public function updateVisibilityAction(UpdatePitchVisibilityRequest $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findParent($id);
        $this->authorize('updateVisibility', $parentAndChild->parent);
        $parentData = $request->json()->all();

        if (! MonitoringHelper::isNewVisibilityValid($parentAndChild->parent, $parentData['visibility'])) {
            throw new MonitoringExistsException();
        }
        $parentAndChild = $this->versionService->updateParentVisibility($parentAndChild->parent->id, $parentData['visibility']);
        MonitoringHelper::progressRelatedMonitoringStages($parentAndChild->parent, $parentData['visibility']);
        $resource = new PitchResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function mostRecentAction(MostRecentActionPitchRequest $request): JsonResponse
    {
        $this->authorize('search', \App\Models\Pitch::class);
        $limit = (int) $request->get('limit', 10);
        $versions = PitchVersionModel::where('status', 'approved')
            ->orderByDesc('approved_rejected_at')
            ->with('parent')
            ->limit($limit)
            ->get();
        $resources = $versions->map(function ($version) {
            return new PitchResource($version->parent, $version);
        });

        return JsonResponseHelper::success($resources, 200);
    }

    public function countByContinentAction(Request $request): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        $dbRecords = PitchVersionModel::approved()
            ->select('land_continent', DB::raw('count(land_continent) as count'))
            ->groupBy('land_continent')
            ->get();

        return JsonResponseHelper::success($dbRecords->values()->toArray(), 200);
    }

    public function readAllByContinentAction(Request $request, string $continent): JsonResponse
    {
        $this->authorize('yes', 'App\\Models\\Default');
        if (! in_array($continent, config('data.continents'))) {
            throw new ModelNotFoundException();
        }
        $versions = PitchVersionModel::approved()
            ->where('land_continent', $continent)
            ->with('parent')
            ->get();
        $resources = $versions->map(function ($version) {
            return new PitchResource($version->parent, $version);
        });

        return JsonResponseHelper::success($resources->values()->toArray(), 200);
    }
}
