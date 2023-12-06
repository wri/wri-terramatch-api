<?php

namespace App\Http\Controllers;

use App\Helpers\ControllerHelper;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StoreDocumentFileRequest;
use App\Http\Requests\StoreProgrammeSubmissionRequest;
use App\Http\Requests\UpdateProgrammeRequest;
use App\Models\Programme;
use App\Models\Submission;
use App\Resources\SubmissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SubmissionController extends Controller
{
    public function createAction(StoreProgrammeSubmissionRequest $request, Programme $programme = null): JsonResponse
    {
        $data = $request->json()->all();
        if (is_null($programme)) {
            $programme = Programme::where('id', $data['programme_id'])->firstOrFail();
            unset($data['programme_id']);
        }
        $this->authorize('create', Submission::class);

        $submission = new Submission($data);
        $submission->programme_id = $programme->id;
        $submission->saveOrFail();

        return JsonResponseHelper::success(new SubmissionResource($submission), 201);
    }

    public function updateAction(UpdateProgrammeRequest $request, Submission $submission): JsonResponse
    {
        $this->authorize('update', $submission);
        $data = $request->all();

        if (isset($data['additional_tree_species'])) {
            $existingFile = $submission->getDocumentFileCollection(['tree_species'])->first();
            if (! empty($existingFile) && $existingFile->id != $data['additional_tree_species']) {
                $existingFile->delete();
            }

            ControllerHelper::callAction('DocumentFileController@createAction', [
                'document_fileable_id' => $submission->id,
                'document_fileable_type' => 'submission',
                'upload' => $data['additional_tree_species'],
                'is_public' => false,
                'title' => 'Additional Tree Species',
                'collection' => 'tree_species',
            ], new StoreDocumentFileRequest());
        }

        $submission->fill(Arr::except($data, ['additional_tree_species']));
        $submission->saveOrFail();

        return JsonResponseHelper::success(new SubmissionResource($submission), 200);
    }

    public function readAction(Submission $submission): JsonResponse
    {
        $this->authorize('read', $submission->programme);

        return JsonResponseHelper::success(new SubmissionResource($submission), 200);
    }

    public function readByProgrammeAction(Programme $programme): JsonResponse
    {
        $this->authorize('read', $programme);

        $resources = [];
        foreach ($programme->submissions as $submission) {
            $resources[] = new SubmissionResource($submission);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function approveAction(Request $request, Submission $submission): JsonResponse
    {
        $this->authorize('approve', $submission);

        $submission->approved_by = Auth::user()->id;
        $submission->approved_at = Carbon::now();
        $submission->saveOrFail();

        return JsonResponseHelper::success(new SubmissionResource($submission), 200);
    }
}
