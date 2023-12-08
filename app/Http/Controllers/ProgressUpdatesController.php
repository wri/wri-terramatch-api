<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidMonitoringException;
use App\Exceptions\InvalidSubmitterException;
use App\Helpers\ErrorHelper;
use App\Helpers\JsonResponseHelper;
use App\Helpers\ProgressUpdateHelper;
use App\Helpers\UploadHelper;
use App\Jobs\CreateThumbnailsJob;
use App\Jobs\NotifyProgressUpdateCreatedJob;
use App\Models\Interest as InterestModel;
use App\Models\Monitoring as MonitoringModel;
use App\Models\ProgressUpdate as ProgressUpdateModel;
use App\Resources\ProgressUpdateResource;
use App\Validators\ProgressUpdateValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProgressUpdatesController extends Controller
{
    /**
     * This method creates a progress update. Progress updates can only be
     * created once the monitoring's targets have been accepted. Even then
     * only users belonging to the organisation which owns the pitch (or admins)
     * can submit progress updates.
     */
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize('create', \App\Models\ProgressUpdate::class);
        $data = $request->json()->all();
        ProgressUpdateValidator::validate('CREATE', $data);

        try {
            ProgressUpdateValidator::validate('CREATE_DATA', $data['data']);
        } catch (ValidationException $exception) {
            $errors = ErrorHelper::prefix($exception->errors(), 'data.');

            return JsonResponseHelper::error($errors, 422);
        }
        $me = Auth::user();
        $images = [];
        foreach ($data['images'] as $key => &$image) {
            try {
                ProgressUpdateValidator::validate('CREATE_IMAGE', $data['images'][$key]);
            } catch (ValidationException $exception) {
                $value = 'images.' . $key . '.';
                $errors = ErrorHelper::prefix($exception->errors(), $value);

                return JsonResponseHelper::error($errors, 422);
            }
            $image['image'] = UploadHelper::findByIdAndValidate(
                $image['image'],
                UploadHelper::IMAGES,
                $me->id
            );
            $image['thumbnail'] = null;
            $images[] = $image['image'];
        }
        UploadHelper::assertUnique(...$images);
        $monitoring = MonitoringModel::findOrFail($data['monitoring_id']);


        $this->authorize('read', $monitoring);
        if ($monitoring->stage != 'accepted_targets') {
            throw new InvalidMonitoringException();
        }
        if ($me->role != 'admin' && $me->role != 'terrafund_admin') {
            $matched = $monitoring->matched;
            $interest = InterestModel::whereIn('id', [$matched->primary_interest_id, $matched->secondary_interest_id])
                ->where('organisation_id', '=', $me->organisation_id)
                ->firstOrFail();
            $submittingAs = $interest->initiator;
            if ($submittingAs != 'pitch') {
                throw new InvalidSubmitterException();
            }
        }
        $data['data'] = ProgressUpdateHelper::total($data['data']);

        $progressUpdate = ProgressUpdateModel::create(array_merge($data, ['created_by' => $me->id]));

        CreateThumbnailsJob::dispatch($progressUpdate);
        NotifyProgressUpdateCreatedJob::dispatch($progressUpdate);
        $resource = new ProgressUpdateResource($progressUpdate);

        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(ProgressUpdateModel $progressUpdate): JsonResponse
    {
        $this->authorize('read', $progressUpdate);
        $resource = new ProgressUpdateResource($progressUpdate);

        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllByMonitoringAction(Request $request, Int $id): JsonResponse
    {
        $progressUpdates = ProgressUpdateModel::where('monitoring_id', '=', $id)
            ->orderBy('created_at')
            ->get();
        $this->authorize('readAll', \App\Models\ProgressUpdate::class);
        $monitoring = MonitoringModel::findOrFail($id);
        $this->authorize('read', $monitoring);
        $resources = [];
        foreach ($progressUpdates as $progressUpdate) {
            $resources[] = new ProgressUpdateResource($progressUpdate);
        }

        return JsonResponseHelper::success($resources, 200);
    }
}
