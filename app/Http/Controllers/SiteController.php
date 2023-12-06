<?php

namespace App\Http\Controllers;

use App\Helpers\ControllerHelper;
use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreAimsRequest;
use App\Http\Requests\StoreControlAimsRequest;
use App\Http\Requests\StoreDocumentFileRequest;
use App\Http\Requests\StoreNarrativeRequest;
use App\Http\Requests\StoreSeedsRequest;
use App\Http\Requests\StoreSiteBoundaryRequest;
use App\Http\Requests\StoreSiteRequest;
use App\Http\Requests\UpdateSiteRequest;
use App\Http\Resources\V2\BaselineMonitoring\SiteMetricResource;
use App\Jobs\CreateDueSubmissionForSiteJob;
use App\Models\Invasive;
use App\Models\LandTenure;
use App\Models\Programme;
use App\Models\SeedDetail;
use App\Models\Site;
use App\Models\SiteRestorationMethod;
use App\Resources\SiteResource;
use App\Validators\SiteValidator;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class SiteController extends Controller
{
    public function createAction(StoreSiteRequest $request): JsonResponse
    {
        $this->authorize('create', Site::class);
        $data = $request->json()->all();
        if (! Auth::user()->programmes->contains($data['programme_id'])) {
            throw new AuthorizationException();
        }

        $data['stratification_for_heterogeneity'] = UploadHelper::findByIdAndValidate(
            $data['stratification_for_heterogeneity'],
            array_merge(UploadHelper::FILES_DOC_PDF, UploadHelper::IMAGES_FILES),
            Auth::user()->id
        );

        $extra = [
            'history' => data_get($data, 'site_history', null),
            'name' => data_get($data, 'site_name', ''),
            'description' => data_get($data, 'site_description', ''),
            'control_site' => data_get($data, 'control_site', false),
        ];

        $site = Site::create(array_merge($data, $extra));

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
        CreateDueSubmissionForSiteJob::dispatch($site, $date);

        return JsonResponseHelper::success(new SiteResource($site), 201);
    }

    public function updateAction(Site $site, UpdateSiteRequest $request): JsonResponse
    {
        $this->authorize('update', $site);
        $data = Arr::except($request->all(), ['programme_id']);

        $siteData = Arr::only($data, ['name', 'description', 'history', 'establishment_date', 'end_date', 'additional_tree_species', 'planting_pattern', 'stratification_for_heterogeneity', 'control_site', 'boundary_geojson']);
        $aimData = Arr::only($data, ['aim_survival_rate', 'aim_year_five_crown_cover', 'aim_direct_seeding_survival_rate', 'aim_natural_regeneration_trees_per_hectare', 'aim_natural_regeneration_hectares', 'aim_number_of_mature_trees', 'aim_soil_condition', 'severely_degraded']);

        if (! empty($siteData['establishment_date'])) {
            $siteData['establishment_date'] = date('Y-m-d', strtotime($siteData['establishment_date']));
        }

        if (! empty($siteData['end_date'])) {
            $siteData['end_date'] = date('Y-m-d', strtotime($siteData['end_date']));
        }

        if (! $site->control_site) {
            if (isset($data['site_land_tenures']) && is_array($data['site_land_tenures'])) {
                $ids = LandTenure::whereIn('key', $data['site_land_tenures'])->pluck('id')->toArray();
                if (count($ids) > 0) {
                    $site->landTenures()->sync($ids);
                }
            }
        }

        if (isset($data['invasives'])) {
            Invasive::where('site_id', $site->id) ->delete();
            foreach ($data['invasives'] as $invasive) {
                Invasive::create([
                    'site_id' => $site->id,
                    'name' => data_get($invasive, 'name'),
                    'type' => data_get($invasive, 'type'),
                ]);
            }
        }

        if (isset($data['site_restoration_methods']) && is_array($data['site_restoration_methods'])) {
            $ids = SiteRestorationMethod::whereIn('key', $data['site_restoration_methods'])->pluck('id')->toArray();
            if (count($ids) > 0) {
                $site->siteRestorationMethods()->sync($ids);
            }
        }

        if (isset($data['seeds'])) {
            SeedDetail::where('site_id', $site->id)->delete();
            $seedDetailController = new SeedDetailController();
            foreach ($data['seeds'] as $seed) {
                $seedDetailController->callAction('createAction', [new StoreSeedsRequest([
                    'site_id' => $site->id,
                    'name' => data_get($seed, 'name'),
                    'weight_of_sample' => data_get($seed, 'weight_of_sample'),
                    'seeds_in_sample' => data_get($seed, 'seeds_in_sample'),
                ]), $site]);
            }
        }


        if (isset($data['stratification_for_heterogeneity'])) {
            $data['stratification_for_heterogeneity'] = UploadHelper::findByIdAndValidate(
                $data['stratification_for_heterogeneity'],
                array_merge(UploadHelper::FILES_DOC_PDF, UploadHelper::IMAGES_FILES),
                Auth::user()->id
            );
        }

        if (isset($data['additional_tree_species'])) {
            $existingFile = $site->getDocumentFileCollection(['tree_species'])->first();
            if (! empty($existingFile) && $existingFile->id != $data['additional_tree_species']) {
                $existingFile->delete();
            }
            ControllerHelper::callAction('DocumentFileController@createAction', [
                'document_fileable_id' => $site->id,
                'document_fileable_type' => 'site',
                'upload' => $data['additional_tree_species'],
                'is_public' => false,

                'title' => 'Additional Tree Species',
                'collection' => 'tree_species',
            ], new StoreDocumentFileRequest());
        }

        $site->update(array_merge($aimData, Arr::except($siteData, ['additional_tree_species'])));

        return JsonResponseHelper::success((object) new SiteResource($site), 200);
    }

    public function readAction(Site $site): JsonResponse
    {
        $this->authorize('read', $site);

        return JsonResponseHelper::success((object) new SiteResource($site), 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('readAll', Site::class);

        if ($request->get('programme_id')) {
            $sites = Site::where('programme_id', $request->get('programme_id'))->paginate(config('app.pagination_default', 15));
        } else {
            $sites = Site::paginate(config('app.pagination_default', 15));
        }

        $resources = [];
        foreach ($sites as $site) {
            $resources[] = new SiteResource($site);
        }

        $meta = (object)[
            'first' => $sites->firstItem(),
            'current' => $sites->currentPage(),
            'last' => $sites->lastPage(),
            'total' => $sites->total(),
        ];

        return JsonResponseHelper::success($resources, 200, $meta);
    }

    public function readAllForUserAction(Request $request): JsonResponse
    {
        $this->authorize('readAllForUser', Site::class);

        $sites = Site::whereIn('programme_id', Auth::user()->programmes->pluck('id'))->paginate(config('app.pagination_default', 15));

        $resources = [];
        foreach ($sites as $site) {
            $resources[] = new SiteResource($site);
        }

        $meta = (object)[
            'first' => $sites->firstItem(),
            'current' => $sites->currentPage(),
            'last' => $sites->lastPage(),
            'total' => $sites->total(),
        ];

        return JsonResponseHelper::success($resources, 200, $meta);
    }

    public function readAllByProgrammeAction(Programme $programme): JsonResponse
    {
        $this->authorize('read', $programme);

        $sites = $programme->sites()->paginate(5);

        $resources = [];
        foreach ($sites as $site) {
            $resources[] = new SiteResource($site);
        }

        $meta = (object)[
            'first' => $sites->firstItem(),
            'current' => $sites->currentPage(),
            'last' => $sites->lastPage(),
            'total' => $sites->total(),
        ];

        return JsonResponseHelper::success($resources, 200, $meta);
    }

    public function readAllNonPaginatedByProgrammeAction(Programme $programme): JsonResponse
    {
        $this->authorize('read', $programme);

        $sites = $programme->sites;

        $resources = [];
        foreach ($sites as $site) {
            $resources[] = new SiteResource($site);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllMetricsByProgrammeAction(Programme $programme): JsonResponse
    {
        $this->authorize('read', $programme);

        $resources = [];
        foreach ($sites = $programme->sites as $site) {
            $metric = $site->baselineMonitoring()->first();
            if (! empty($metric)) {
                $resources[] = new SiteMetricResource($metric);
            }
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function addBoundaryToSiteAction(StoreSiteBoundaryRequest $request): JsonResponse
    {
        $this->authorize('addBoundary', Site::class);
        $data = $request->all();

        $site = Site::where('id', $data['site_id'])->firstOrFail();

        $site->update(['boundary_geojson' => $data['boundary_geojson']]);

        return JsonResponseHelper::success(new SiteResource($site), 200);
    }

    public function attachRestorationMethodsAction(Request $request, Site $site = null): JsonResponse
    {
        $data = $request->json()->all();
        if (is_null($site)) {
            $site = Site::where('id', $data['site_id'])->firstOrFail();
            unset($data['site_id']);
        }

        $this->authorize('update', $site);
        SiteValidator::validate('ATTACH_RESTORATION_METHODS', $data);

        $site->siteRestorationMethods()->sync($data['site_restoration_method_ids']);

        return JsonResponseHelper::success(new SiteResource($site), 201);
    }

    public function attachLandTenureAction(Request $request, Site $site = null): JsonResponse
    {
        $data = $request->json()->all();
        if (is_null($site)) {
            $site = Site::where('id', $data['site_id'])->firstOrFail();
            unset($data['site_id']);
        }

        $this->authorize('update', $site);
        SiteValidator::validate('ATTACH_LAND_TENURE', $data);

        $site->landTenures()->sync($data['land_tenure_ids']);

        return JsonResponseHelper::success(new SiteResource($site), 201);
    }

    public function updateEstablishmentDateAction(Request $request, Site $site = null): JsonResponse
    {
        $data = $request->json()->all();
        if (is_null($site)) {
            $site = Site::where('id', $data['site_id'])->firstOrFail();
            unset($data['site_id']);
        }

        $this->authorize('update', $site);
        SiteValidator::validate('UPDATE_ESTABLISHMENT_DATE', $data);

        $site->update(['establishment_date' => $data['establishment_date']]);

        return JsonResponseHelper::success(new SiteResource($site), 201);
    }

    public function createNarrativeAction(StoreNarrativeRequest $request, Site $site = null): JsonResponse
    {
        $data = $request->all();
        if (is_null($site)) {
            $site = Site::where('id', $data['site_id'])->firstOrFail();
            unset($data['site_id']);
        }

        $this->authorize('update', $site);

        $site->update($data);

        return JsonResponseHelper::success((object) new SiteResource($site), 201);
    }

    public function createAimAction(StoreAimsRequest $request, Site $site = null): JsonResponse
    {
        $data = $request->all();
        if (is_null($site)) {
            $site = Site::where('id', $data['site_id'])->firstOrFail();
            unset($data['site_id']);
        }

        $this->authorize('update', $site);

        $site = $this->createAims($data, $site);

        return JsonResponseHelper::success((object) new SiteResource($site), 201);
    }

    public function createControlAimAction(StoreControlAimsRequest $request, Site $site = null): JsonResponse
    {
        $data = $request->all();
        if (is_null($site)) {
            $site = Site::where('id', $data['site_id'])->firstOrFail();
            unset($data['site_id']);
        }

        $this->authorize('update', $site);

        $site = $this->createAims($data, $site);

        return JsonResponseHelper::success((object) new SiteResource($site), 201);
    }

    public function createControlAimsAction(StoreControlAimsRequest $request, Site $site = null): JsonResponse
    {
        $data = $request->all();
        if (is_null($site)) {
            $site = Site::where('id', $data['site_id'])->firstOrFail();
            unset($data['site_id']);
        }

        $this->authorize('update', $site);

        $site = $this->createAims($data, $site);

        return JsonResponseHelper::success((object) new SiteResource($site), 201);
    }

    private function createAims(array $data, Site $site): Site
    {
        if (isset($data['aim_survival_rate'])) {
            $site->aim_survival_rate = $data['aim_survival_rate'];
        }
        if (isset($data['aim_direct_seeding_survival_rate'])) {
            $site->aim_direct_seeding_survival_rate = $data['aim_direct_seeding_survival_rate'];
        }
        if (isset($data['aim_natural_regeneration_trees_per_hectare'])) {
            $site->aim_natural_regeneration_trees_per_hectare = $data['aim_natural_regeneration_trees_per_hectare'];
        }
        if (isset($data['aim_natural_regeneration_hectares'])) {
            $site->aim_natural_regeneration_hectares = $data['aim_natural_regeneration_hectares'];
        }
        if (isset($data['aim_soil_condition'])) {
            $site->aim_soil_condition = $data['aim_soil_condition'];
        }
        if (isset($data['aim_number_of_mature_trees'])) {
            $site->aim_number_of_mature_trees = $data['aim_number_of_mature_trees'];
        }
        $site->aim_year_five_crown_cover = $data['aim_year_five_crown_cover'];
        $site->saveOrFail();

        return $site;
    }
}
