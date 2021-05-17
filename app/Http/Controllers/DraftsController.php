<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidJsonPatchException;
use App\Exceptions\InvalidUploadTypeException;
use App\Exceptions\UploadNotFoundException;
use App\Helpers\DraftHelper;
use App\Helpers\JsonPatchHelper;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Jobs\PublishDraftOfferJob;
use App\Jobs\PublishDraftPitchJob;
use App\Models\Draft as DraftModel;
use App\Resources\DraftResource;
use App\Validators\DraftValidator;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Pluralizer;
use Swaggest\JsonDiff\JsonPatch;
use Throwable;

class DraftsController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $this->authorize("create", "App\\Models\\Draft");
        $data = $request->json()->all();
        DraftValidator::validate("CREATE", $data);
        $me = Auth::user();
        $draft = new DraftModel($data);
        switch ($draft->type) {
            case "offer":
                $draft->data = json_encode(DraftHelper::EMPTY_DATA_OFFER);
                break;
            case "pitch":
                $draft->data = json_encode(DraftHelper::EMPTY_DATA_PITCH);
                break;
        }
        $draft->organisation_id = $me->organisation_id;
        $draft->created_by = $me->id;
        $draft->saveOrFail();
        $draft->refresh();
        $resource = new DraftResource($draft);
        return JsonResponseHelper::success($resource, 201);
    }

    public function readAction(Request $request, Int $id): JsonResponse
    {
        $draft = DraftModel::findOrFail($id);
        $this->authorize("read", $draft);
        $resource = new DraftResource($draft);
        return JsonResponseHelper::success($resource, 200);
    }

    public function readAllByTypeAction(Request $request, String $type): JsonResponse
    {
        $this->authorize("readAll", "App\\Models\\Draft");
        $me = Auth::user();
        $drafts = DraftModel
            ::where("type", "=", Pluralizer::singular($type))
            ->where("organisation_id", "=", $me->organisation_id)
            ->get();
        $resources = [];
        foreach ($drafts as $draft) {
            $resources[] = new DraftResource($draft);
        }
        return JsonResponseHelper::success($resources, 200);
    }

    public function updateAction(Request $request, Int $id): JsonResponse
    {
        $draft = DraftModel::findOrFail($id);
        $this->authorize("update", $draft);
        $me = Auth::user();
        $data = json_decode($draft->data);
        /**
         * This section manually extracts the JSON Patch from the body of the
         * request. If we use Laravel's helper it will come back as an
         * associative array, which is not valid JSON Patch.
         */
        $patch = json_decode($request->getContent());
        if (!is_array($patch)) {
            throw new InvalidJsonPatchException();
        }
        /**
         * This section attempts to reorder the remove ops. If for any reason
         * the method errors (and it might as we haven't validated the JSON
         * Patch yet), we should attempt to continue as normal, hence the empty
         * catch.
         */
        try {
            $patch = JsonPatchHelper::reorderRemoveOps($patch);
        } catch (Throwable $thrown) {
        }
        try {
            JsonPatch::import($patch)->apply($data);
        } catch (Exception $exception) {
            throw new InvalidJsonPatchException();
        }
        /**
         * This section converts $data from an object into an associative
         * array. Laravel's validators only works on associative arrays as
         * that's what PHP would normally return in $_POST.
         */
        $arrayData = json_decode(json_encode($data), true);
        DraftValidator::validate("UPDATE_DATA_" . strtoupper($draft->type), $arrayData);
        /**
         * This section extracts the uploads, asserts they are all unique, and
         * then iterates over them. It is asserted that every upload belongs to
         * the same organisation as the current user may not be the user who
         * originally created the draft. It is also asserted that every every
         * upload is not a TIFF since those are only used by admins when creating
         * satellite maps.
         */
        $uploads = DraftHelper::extractUploads($draft->type, $data);
        UploadHelper::assertUnique(...$uploads);
        foreach ($uploads as $upload) {
            if ($upload->user->organisation_id != $me->organisation_id) {
                throw new UploadNotFoundException();
            } else if (pathinfo($upload->location, PATHINFO_EXTENSION) == "tiff") {
                throw new InvalidUploadTypeException();
            }
        }
        $draft->data = json_encode($data);
        $draft->updated_by = $me->id;
        $draft->saveOrFail();
        $draft->refresh();
        $resource = new DraftResource($draft);
        return JsonResponseHelper::success($resource, 200);
    }

    public function deleteAction(Request $request, Int $id): JsonResponse
    {
        $draft = DraftModel::findOrFail($id);
        $this->authorize("delete", $draft);
        $draft->delete();
        return JsonResponseHelper::success((object) [], 200);
    }

    public function publishAction(Request $request, Int $id): JsonResponse
    {
        $draft = DraftModel::findOrFail($id);
        $this->authorize("publish", $draft);
        $me = Auth::user();
        switch ($draft->type) {
            case "offer":
                $id = PublishDraftOfferJob::dispatchNow($me, $draft);
                break;
            case "pitch":
                $id = PublishDraftPitchJob::dispatchNow($me, $draft);
                break;
        }
        $key = $draft->type . "_id";
        return JsonResponseHelper::success((object) [$key => $id], 201);
    }
}
