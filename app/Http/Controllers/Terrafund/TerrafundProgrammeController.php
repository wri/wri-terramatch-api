<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\StoreTerrafundProgrammeRequest;
use App\Http\Requests\Terrafund\StoreTerrafundTreeSpeciesRequest;
use App\Http\Requests\Terrafund\UpdateTerrafundProgrammeRequest;
use App\Models\Organisation;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundTreeSpecies;
use App\Models\V2\User;
use App\Resources\Terrafund\TerrafundProgrammeLiteResource;
use App\Resources\Terrafund\TerrafundProgrammeResource;
use App\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class TerrafundProgrammeController extends Controller
{
    public function createAction(StoreTerrafundProgrammeRequest $request): JsonResponse
    {
        $this->authorize('create', TerrafundProgramme::class);
        $data = $request->json()->all();

        $extra = [
            'framework_id' => 2, // Terrafund
            'organisation_id' => Auth::user()->organisation->id,
        ];

        $programme = TerrafundProgramme::create(array_merge($data, $extra));

        Auth::user()->terrafundProgrammes()->sync($programme->id, false);

        return JsonResponseHelper::success(new TerrafundProgrammeResource($programme), 201);
    }

    public function updateAction(UpdateTerrafundProgrammeRequest $request, TerrafundProgramme $terrafundProgramme): JsonResponse
    {
        $data = $request->all();
        $this->authorize('update', $terrafundProgramme);

        if (! empty(data_get($data, 'tree_species'))) {
            TerrafundTreeSpecies::where('treeable_type', TerrafundProgramme::class)
                ->where('treeable_id', data_get($terrafundProgramme, 'id'))
                ->delete();

            foreach (data_get($data, 'tree_species')as $treeSpeciesData) {
                $payload = [
                    'treeable_type' => 'programme',
                    'treeable_id' => data_get($terrafundProgramme, 'id'),
                    'name' => data_get($treeSpeciesData, 'name'),
                    'amount' => data_get($treeSpeciesData, 'amount'),
                ];
                $controller = new TerrafundTreeSpeciesController();
                $controller->callAction('createAction', [new StoreTerrafundTreeSpeciesRequest($payload)]);
            }
        }
        $terrafundProgramme->fill(Arr::except($data, ['tree_species', 'additional_files']));
        $terrafundProgramme->saveOrFail();

        return JsonResponseHelper::success(new TerrafundProgrammeResource($terrafundProgramme), 200);
    }

    public function readAction(TerrafundProgramme $terrafundProgramme): JsonResponse
    {
        $this->authorize('read', $terrafundProgramme);

        return JsonResponseHelper::success(new TerrafundProgrammeResource($terrafundProgramme), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', TerrafundProgramme::class);

        $programmes = TerrafundProgramme::all();
        $resources = [];
        foreach ($programmes as $programme) {
            $resources[] = new TerrafundProgrammeLiteResource($programme);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllPartnersAction(TerrafundProgramme $terrafundProgramme): JsonResponse
    {
        $this->authorize('read', $terrafundProgramme);

        $resources = [];
        foreach ($terrafundProgramme->users as $partner) {
            $resources[] = new UserResource($partner);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllPersonalAction(Request $request): JsonResponse
    {
        $this->authorize('readAllPersonal', TerrafundProgramme::class);

        $programmes = ! isset($request->organisation_id) ?
            Auth::user()->terrafundProgrammes :
            Auth::user()->terrafundProgrammes->where('organisation_id', $request->organisation_id);

        $resources = [];
        foreach ($programmes as $programme) {
            $resources[] = new TerrafundProgrammeLiteResource($programme);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllForOrgAction(Request $request, Organisation $organisation): JsonResponse
    {
        $this->authorize('readAllForOrg', TerrafundProgramme::class);

        $programmes = $organisation->terrafundProgrammes;
        $resources = [];
        foreach ($programmes as $programme) {
            $resources[] = new TerrafundProgrammeResource($programme);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function deletePartnerAction(TerrafundProgramme $terrafundProgramme, User $user): JsonResponse
    {
        $this->authorize('deletePartner', $terrafundProgramme);

        $user->terrafundProgrammes()->detach([$terrafundProgramme->id]);

        return JsonResponseHelper::success((object) [], 200);
    }
}
