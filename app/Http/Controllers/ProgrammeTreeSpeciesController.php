<?php

namespace App\Http\Controllers;

use App\Clients\TreeSpeciesClient;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\StoreBulkTreeSpeciesRequest;
use App\Http\Requests\StoreBulkTreeSpeciesWithAmountRequest;
use App\Http\Requests\StoreProgrammeTreeSpeciesRequest;
use App\Http\Resources\AllProgrammeTreeSpeciesResource;
use App\Models\Programme;
use App\Models\ProgrammeTreeSpecies;
use App\Models\Submission;
use App\Resources\BaseProgrammeTreeSpeciesResource;
use App\Resources\ProgrammeResource;
use App\Resources\ProgrammeTreeSpeciesResource;
use App\Resources\SubmissionResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgrammeTreeSpeciesController extends Controller
{
    public function createAction(StoreProgrammeTreeSpeciesRequest $request, Programme $programme = null): JsonResponse
    {
        $data = $request->json()->all();
        if (is_null($programme)) {
            if (! isset($data['programme_id'])) {
                throw new ModelNotFoundException();
            }
            $programme = Programme::where('id', $data['programme_id'])->firstOrFail();
        }
        $this->authorize('createTreeSpecies', $programme);

        $programmeTreeSpecies = new ProgrammeTreeSpecies($data);
        $programmeTreeSpecies->name = $data['name'];
        $programmeTreeSpecies->programme_id = $programme->id;
        if (isset($data['programme_submission_id'])) {
            $programmeTreeSpecies->programme_submission_id = $data['programme_submission_id'];
            $programmeTreeSpecies->amount = $data['amount'];
        }
        $programmeTreeSpecies->saveOrFail();

        return JsonResponseHelper::success((object) new ProgrammeTreeSpeciesResource($programmeTreeSpecies), 201);
    }

    public function createBulkAction(Programme $programme, StoreBulkTreeSpeciesRequest $request): JsonResponse
    {
        $data = $request->json()->all();
        $this->authorize('createTreeSpecies', $programme);

        $programme->programmeTreeSpecies()->delete();

        if (isset($data['tree_species']) && $data['tree_species']) {
            foreach ($data['tree_species'] as $species) {
                $programmeTreeSpecies = new ProgrammeTreeSpecies();
                $programmeTreeSpecies->programme_id = $programme->id;
                $programmeTreeSpecies->name = $species['name'];
                $programmeTreeSpecies->saveOrFail();
            }
        }

        $programme->refresh();

        return JsonResponseHelper::success(new ProgrammeResource($programme), 201);
    }

    public function createBulkForSubmissionAction(Submission $submission, StoreBulkTreeSpeciesWithAmountRequest $request): JsonResponse
    {
        $data = $request->json()->all();
        $this->authorize('createTreeSpecies', $submission->programme);

        $submission->programmeTreeSpecies()->delete();

        if (isset($data['tree_species']) && $data['tree_species']) {
            foreach ($data['tree_species'] as $species) {
                $programmeTreeSpecies = new ProgrammeTreeSpecies();
                $programmeTreeSpecies->programme_id = $submission->programme_id;
                $programmeTreeSpecies->programme_submission_id = $submission->id;
                $programmeTreeSpecies->name = $species['name'];
                $programmeTreeSpecies->amount = $species['amount'];
                $programmeTreeSpecies->saveOrFail();
            }
        }

        $submission->refresh();

        return JsonResponseHelper::success(new SubmissionResource($submission), 201);
    }

    public function deleteAction(ProgrammeTreeSpecies $programmeTreeSpecies): JsonResponse
    {
        $this->authorize('delete', $programmeTreeSpecies);
        $programmeTreeSpecies->delete();

        return JsonResponseHelper::success((object) [], 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('readAllTreeSpecies', Programme::class);

        $programmes = Auth::user()->programmes;
        $resources = [];
        foreach ($programmes as $programme) {
            $treeSpecies = [];
            foreach ($programme->programmeTreeSpecies->unique('name') as $programmeTreeSpecies) {
                $treeSpecies[] = new BaseProgrammeTreeSpeciesResource($programmeTreeSpecies);
            }
            if (! empty($treeSpecies)) {
                $resources[] = new AllProgrammeTreeSpeciesResource($treeSpecies);
            }
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllByProgrammeAction(Programme $programme): JsonResponse
    {
        $this->authorize('readAllTreeSpecies', $programme);
        $this->authorize('read', $programme);

        $resources = [];
        $establishmentOnly = $programme->programmeTreeSpecies()->whereNull('programme_submission_id')->get();
        foreach ($establishmentOnly as $programmeTreeSpecies) {
            $resources[] = new ProgrammeTreeSpeciesResource($programmeTreeSpecies);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function searchTreeSpeciesAction(SearchRequest $request): JsonResponse
    {
        $this->authorize('readAllTreeSpecies', Programme::class);

        $client = app()->make(TreeSpeciesClient::class);
        $results = $client->search($request->get('search_term'));
        $resources = [];
        foreach ($results as $result) {
            $resources[] = $result->Name_matched;
        }

        return JsonResponseHelper::success(array_values(array_unique($resources)), 200);
    }
}
