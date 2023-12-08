<?php

namespace App\Http\Controllers;

use App\Helpers\ArrayHelper;
use App\Helpers\JsonResponseHelper;
use App\Jobs\NotifyVersionApprovedJob;
use App\Jobs\NotifyVersionRejectedJob;
use App\Models\Organisation as OrganisationModel;
use App\Models\OrganisationVersion as OrganisationVersionModel;
use App\Models\V2\Organisation as V2Organisation;
use App\Resources\OrganisationV2ToV1Resource;
use App\Resources\OrganisationVersionResource;
use App\Services\Version\VersionService;
use App\Validators\VersionValidator as OrganisationVersionValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganisationVersionsController extends Controller
{
    protected $versionService = null;

    public function __construct(
        OrganisationModel $organisationModel,
        OrganisationVersionModel $organisationVersionModel
    ) {
        $this->versionService = new VersionService($organisationModel, $organisationVersionModel);
    }

    public function readAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize('read', $parentAndChild->child);
        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllByOrganisationAction(Request $request, int $id): JsonResponse
    {
        $parentAndChildren = $this->versionService->findAllChildren($id);
        $this->authorize('readAllBy', $parentAndChildren->parent);
        $resources = [];

        foreach ($parentAndChildren->children as $child) {
            $resources[] = new OrganisationVersionResource($parentAndChildren->parent, $child);
        }

        if (count($resources) == 0) {
            //this is a v2.0 organisation
            $organisation = V2Organisation::find($id);
            if (! empty($organisation)) {
                $resources[] = new OrganisationV2ToV1Resource($organisation);

                return JsonResponseHelper::success($resources, 200);
            }
        }

        $resources = ArrayHelper::sortDataBy($resources, 'created_at', ArrayHelper::DESC);

        return JsonResponseHelper::success($resources, 200);
    }

    public function approveAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize('approve', $parentAndChild->child);
        $me = Auth::user();
        $parentAndChild = $this->versionService->approveChild($id, $me->id);
        NotifyVersionApprovedJob::dispatch($parentAndChild->child);
        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function rejectAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize('reject', $parentAndChild->child);
        $data = $request->json()->all();
        OrganisationVersionValidator::validate('REJECT', $data);
        $me = Auth::user();
        $parentAndChild = $this->versionService->rejectChild($id, $me->id, $data['rejected_reason'], $data['rejected_reason_body']);
        NotifyVersionRejectedJob::dispatch($parentAndChild->child);
        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }

    public function deleteAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize('delete', $parentAndChild->child);
        $this->versionService->deleteChild($parentAndChild->child->id);

        return JsonResponseHelper::success((object) [], 200);
    }

    /**
     * This method allows an admin to approve a rejected version. On the surface
     * this sounds like it would be counter intuitive, but it's required in
     * certain situations. For example, when the first version of something is
     * rejected it's children somethings are also rejected. However when the
     * second version of something is then approved its first version children
     * somethings need to be approved as well... if this doesn't happen the user
     * will think they've lost their first version children somethings!
     * Unless the user has made a change to those first version children
     * somethings (and by proxy created second version children somethings) that
     * won't happen on its own. Thus this method was created.
     */
    public function reviveAction(Request $request, int $id): JsonResponse
    {
        $parentAndChild = $this->versionService->findChild($id);
        $this->authorize('revive', $parentAndChild->child);
        $parentAndChild = $this->versionService->updateChild($parentAndChild->parent->id, $parentAndChild->child->toArray());
        $me = Auth::user();
        $parentAndChild = $this->versionService->approveChild($parentAndChild->child->id, $me->id);
        NotifyVersionApprovedJob::dispatch($parentAndChild->child);
        $resource = new OrganisationVersionResource($parentAndChild->parent, $parentAndChild->child);

        return JsonResponseHelper::success($resource, 200);
    }
}
