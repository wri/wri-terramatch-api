<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\V2Helper;
use App\Http\Requests\EditHistory\EditHistoryApproveRequest;
use App\Http\Requests\EditHistory\EditHistoryCreateRequest;
use App\Http\Requests\EditHistory\EditHistoryRejectRequest;
use App\Http\Requests\EditHistory\EditHistoryUpdateRequest;
use App\Http\Requests\Terrafund\UpdateTerrafundNurseryRequest;
use App\Http\Requests\Terrafund\UpdateTerrafundProgrammeRequest;
use App\Http\Requests\Terrafund\UpdateTerrafundSiteRequest;
use App\Http\Requests\UpdateProgrammeRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Http\Resources\EditHistoryResource;
use App\Models\EditHistory;
use App\Models\Framework;
use App\Models\Notification as NotificationModel;
use App\Models\OrganisationVersion;
use App\Models\Programme;
use App\Models\Site;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundSite;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EditHistoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('view', EditHistory::class);

        $qry = EditHistory::query();

        if ($request->query('status')) {
            $qry->isInStatus(explode(',', $request->query('status')));
        }

        $perPage = $request->query('items') ?? config('app.pagination_default', 15);
        $order = $request->query('order') ?? 'desc';

        if ($request->query('search')) {
            $qry->search($request->query('search'));
        }

        switch ($request->query('sort')) {
            case 'organisation':
                $qry->orderBy(
                    OrganisationVersion::select('name')
                        ->whereColumn('organisation_id', 'edit_histories.organisation_id')
                        ->orderBy('name', $order)
                        ->limit(1),
                    $order
                );

                break;
            case 'project':
                $qry->orderBy('project_name', $order);

                break;
            case 'framework':
                $qry->orderBy(
                    Framework::select('name')
                        ->whereColumn('id', 'edit_histories.framework_id')
                        ->orderBy('name', $order)
                        ->limit(1),
                    $order
                );

                break;
            case 'type':
                $qry->orderBy('editable_type', $order);

                break;
            case 'updated':
                $qry->orderBy('updated_at', $order);

                break;
            default:
                $qry->orderBy('updated_at', $order);
        }

        $collection = $qry->paginate($perPage);

        $resources = [];
        foreach ($collection  as $edit) {
            $resources[] = new EditHistoryResource($edit);
        }

        $meta = (object)[
            'first' => $collection->firstItem(),
            'current' => $collection->currentPage(),
            'last' => $collection->lastPage(),
            'total' => $collection->total(),
            'per_page' => $collection->perPage(),
        ];

        return JsonResponseHelper::success($resources, 200, $meta);
    }

    public function view(string $uuid): EditHistoryResource
    {
        $record = EditHistory::isUuid($uuid)->first();

        if (empty($record)) {
            throw new ModelNotFoundException();
        }

        $this->authorize('view',  $record);

        return new EditHistoryResource($record);
    }

    public function viewLatestForModel(string $type, int $id)
    {
        $model = V2Helper::getModel($type, $id);

        if (empty($model)) {
            throw new ModelNotFoundException();
        }

        $record = EditHistory::where('editable_type', get_class($model))
            ->where('editable_id', $model->id)
            ->latest()
            ->first();

        if (empty($record)) {
            return response()->json(null);
        }

        return new EditHistoryResource($record);
    }

    public function update(EditHistoryUpdateRequest $request, string $uuid): EditHistoryResource
    {
        $validated = $request->validated();
        $record = EditHistory::isUuid($uuid)->first();

        if (empty($record)) {
            throw new ModelNotFoundException();
        }

        $this->authorize('update',  $record);

        $validated['status'] = EditHistory::STATUS_REQUESTED;
        $record->update($validated);

        return new EditHistoryResource($record->fresh());
    }

    public function store(EditHistoryCreateRequest $request): ?EditHistoryResource
    {
        $validated = $request->validated();
        $model = V2Helper::getModel($validated['editable_type'], $validated['editable_id']);
        $this->authorize('create', EditHistory::class);

        $validated['editable_type'] = get_class($model);
        $validated['editable_id'] = $model->id;
        $validated['status'] = EditHistory::STATUS_REQUESTED;
        $validated['created_by_user_id'] = Auth::user()->id;

        $project = V2Helper::getProject($model);

        if (! empty($project)) {
            $validated['projectable_type'] = get_class($project);
            $validated['projectable_id'] = $project->id;
            $validated['project_name'] = $project->name;
        }

        $organisation = V2Helper::getOrganisation($model);
        if (! empty($organisation)) {
            $validated['organisation_id'] = $organisation->id;
        }

        $framework = V2Helper::getFramework($model);

        if (! empty($framework)) {
            $validated['framework_id'] = $framework->id;
        }

        $editHistory = EditHistory::create($validated);

        return new EditHistoryResource($editHistory->fresh());
    }

    public function approve(EditHistoryApproveRequest $request): EditHistoryResource
    {
        $validated = $request->validated();

        $this->authorize('changeStatus', EditHistory::class);

        $record = EditHistory::isUuid($validated['uuid'])->first();

        if (empty($record)) {
            throw new ModelNotFoundException();
        }
        $record->status = EditHistory::STATUS_APPROVED;
        $record->save();
        $payload = (array) json_decode(data_get($record, 'content'));
        $this->updateOriginal($record->editable, $payload);

        $notification = new NotificationModel([
            'user_id' => $record->created_by_user_id,
            'title' => 'Approved Update Request',
            'body' => 'Your update request as been approved',
            'action' => 'approved_edit',
            'referenced_model' => $record->editable_type,
            'referenced_model_id' => $record->editable_id,
        ]);
        $notification->saveOrFail();

        return new EditHistoryResource($record->fresh());
    }

    public function reject(EditHistoryRejectRequest $request): EditHistoryResource
    {
        $validated = $request->validated();
        $this->authorize('changeStatus', EditHistory::class);

        $record = EditHistory::isUuid($validated['uuid'])->first();

        if (empty($record)) {
            throw new ModelNotFoundException();
        }
        $record->status = EditHistory::STATUS_REJECTED;
        $record->comments = data_get($validated, 'comments', '');
        $record->save();

        $notification = new NotificationModel([
            'user_id' => $record->created_by_user_id,
            'title' => 'Rejected Update Request',
            'body' => 'Your update request as been rejected',
            'action' => 'rejected_edit',
            'referenced_model' => $record->editable_type,
            'referenced_model_id' => $record->editable_id,
        ]);
        $notification->saveOrFail();

        return new EditHistoryResource($record);
    }

    private function updateOriginal($model, $data)
    {
        switch (get_class($model)) {
            case Programme::class:
                $controller = new ProgrammeController();
                $controller->callAction('updateAction', [$model, new UpdateProgrammeRequest($data)]);

                break;
            case Site::class:
                $controller = new SiteController();
                $controller->callAction('updateAction',  [$model, new UpdateSiteRequest($data)]);

                break;
            case TerrafundProgramme::class:
                $controller = new Terrafund\TerrafundProgrammeController();
                $controller->callAction('updateAction', [ new UpdateTerrafundProgrammeRequest($data), $model]);

                break;
            case TerrafundSite::class:
                $controller = new Terrafund\TerrafundSiteController();
                $controller->callAction('updateAction', [new UpdateTerrafundSiteRequest($data), $model]);

                break;
            case TerrafundNursery::class:
                $controller = new Terrafund\TerrafundNurseryController();
                $controller->callAction('updateAction', [new UpdateTerrafundNurseryRequest($data), $model]);

                break;
        }
    }
}
