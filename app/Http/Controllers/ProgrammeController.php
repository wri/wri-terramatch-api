<?php

namespace App\Http\Controllers;

use App\Helpers\ControllerHelper;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreDocumentFileRequest;
use App\Http\Requests\StoreProgrammeBoundaryRequest;
use App\Http\Requests\StoreProgrammeRequest;
use App\Http\Requests\UpdateProgrammeRequest;
use App\Jobs\CreateDueSubmissionForProgrammeJob;
use App\Models\Aim;
use App\Models\Programme;
use App\Resources\ProgrammeLiteResource;
use App\Resources\ProgrammeResource;
use App\Resources\SubmissionResource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class ProgrammeController extends Controller
{
    public function readAction(Programme $programme): JsonResponse
    {
        $this->authorize('read', $programme);

        $submissionResources = [];
        foreach ($programme->submissions as $submission) {
            $submissionResources[] = new SubmissionResource($submission);
        }
        $numberOfSites = $programme->sites()->count();

        return JsonResponseHelper::success((object) new ProgrammeResource($programme, $submissionResources, $numberOfSites), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', Programme::class);

        //        $programmes = Programme::with('submissions', 'documentFiles', 'baselineMonitoring')->get();
        $programmes = Programme::all();
        $resources = [];
        foreach ($programmes as $programme) {
            $resources[] = new ProgrammeLiteResource($programme);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllPersonalAction(Request $request): JsonResponse
    {
        $this->authorize('readAllPersonal', Programme::class);

        $programmes = Auth::user()->programmes;
        $resources = [];
        foreach ($programmes as $programme) {
            $resources[] = new ProgrammeLiteResource($programme);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function createAction(StoreProgrammeRequest $request): JsonResponse
    {
        $this->authorize('create', Programme::class);
        $data = $request->json()->all();

        if (isset($data['thumbnail'])) {
            $data['thumbnail'] = UploadHelper::findByIdAndValidate(
                $data['thumbnail'],
                UploadHelper::IMAGES,
                Auth::user()->id
            );
        }

        $extra = [
            'organisation_id' => Auth::user()->organisation->id,
            'framework_id' => 1,
        ];

        $programme = Programme::create(array_merge($data, $extra));

        Auth::user()->programmes()->attach($programme->id);

        $currentMonth = now()->month;
        if ($currentMonth <= 3) {
            $carbonDate = Carbon::createFromFormat('m', 4);
        } elseif ($currentMonth >= 3 && $currentMonth <= 6) {
            $carbonDate = Carbon::createFromFormat('m', 7);
        } elseif ($currentMonth >= 6 && $currentMonth <= 9) {
            $carbonDate = Carbon::createFromFormat('m', 10);
        } elseif ($currentMonth >= 9 && $currentMonth <= 12) {
            $carbonDate = Carbon::createFromFormat('m', 1);
        }

        $date = $carbonDate->isPast() ? $carbonDate->addYear()->firstOfMonth(5) : $carbonDate->firstOfMonth(5);

        CreateDueSubmissionForProgrammeJob::dispatch($programme, $date);

        return JsonResponseHelper::success(new ProgrammeResource($programme), 201);
    }

    public function updateAction(Programme $programme, UpdateProgrammeRequest $request): JsonResponse
    {
        $this->authorize('update', $programme);
        $data = $request->all();

        if (isset($data['thumbnail'])) {
            $programme->thumbnail = UploadHelper::findByIdAndValidate(
                $data['thumbnail'],
                UploadHelper::IMAGES,
                Auth::user()->id
            );
        }

        if (isset($data['additional_tree_species'])) {
            $existingFile = $programme->getDocumentFileCollection(['tree_species'])->first();
            if (! empty($existingFile) && $existingFile->id != $data['additional_tree_species']) {
                $existingFile->delete();
            }

            ControllerHelper::callAction('DocumentFileController@createAction', [
                'document_fileable_id' => $programme->id,
                'document_fileable_type' => 'programme',
                'upload' => $data['additional_tree_species'],
                'is_public' => false,
                'title' => 'Additional Tree Species',
                'collection' => 'tree_species',
            ], new StoreDocumentFileRequest());
        }

        if (isset($data['aims'])) {
            $aimsPayload = Arr::only((array) $data['aims'], ['year_five_trees', 'restoration_hectares', 'survival_rate', 'year_five_crown_cover']);

            $aim = $programme->aim;
            if (! empty($aim)) {
                $aim->fill($aimsPayload);
                $aim->save();
            } else {
                Aim::create(array_merge(['programme_id' => $programme->id ], $aimsPayload));
            }
        }

        $programme->fill(Arr::except($data, ['additional_tree_species', 'aims']));
        $programme->saveOrFail();

        return JsonResponseHelper::success(new ProgrammeResource($programme), 200);
    }

    public function addBoundaryToProgrammeAction(StoreProgrammeBoundaryRequest $request): JsonResponse
    {
        $this->authorize('addBoundary', Programme::class);
        $data = $request->json()->all();

        $programme = Programme::where('id', $data['programme_id'])->firstOrFail();
        $programme->boundary_geojson = $data['boundary_geojson'];
        $programme->saveOrFail();

        return JsonResponseHelper::success(new ProgrammeResource($programme), 200);
    }
}
