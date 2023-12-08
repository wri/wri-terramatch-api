<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StoreBulkSiteTreeSpeciesRequest;
use App\Http\Requests\StoreSiteTreeSpeciesRequest;
use App\Http\Resources\AllSiteTreeSpeciesResource;
use App\Models\Site;
use App\Models\SiteSubmission;
use App\Models\SiteTreeSpecies;
use App\Resources\BaseSiteTreeSpeciesResource;
use App\Resources\SiteResource;
use App\Resources\SiteTreeSpeciesResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiteTreeSpeciesController extends Controller
{
    public function createAction(StoreSiteTreeSpeciesRequest $request, Site $site = null): JsonResponse
    {
        $data = $request->all();
        if (is_null($site)) {
            $site = Site::where('id', $data['site_id'])->firstOrFail();
        }

        $this->authorize('update', $site);

        $siteTreeSpecies = new SiteTreeSpecies();
        $siteTreeSpecies->site_id = $data['site_id'];
        if (isset($data['site_submission_id'])) {
            $siteTreeSpecies->site_submission_id = $data['site_submission_id'];
            $siteTreeSpecies->amount = $data['amount'];
        }
        $siteTreeSpecies->name = $data['name'];
        $siteTreeSpecies->saveOrFail();

        return JsonResponseHelper::success(new SiteTreeSpeciesResource($siteTreeSpecies), 200);
    }

    public function createBulkAction(StoreBulkSiteTreeSpeciesRequest $request, Site $site): JsonResponse
    {
        $data = $request->all();

        $this->authorize('update', $site);

        if (isset($data['site_submission_id'])) {
            $siteSubmission = SiteSubmission::where('id', $data['site_submission_id'])->firstOrFail();
            $siteSubmission->siteTreeSpecies()->delete();
        } else {
            $site->siteTreeSpecies()->delete();
        }

        if (isset($data['tree_species']) && $data['tree_species']) {
            foreach ($data['tree_species'] as $species) {
                $siteTreeSpecies = new SiteTreeSpecies();
                $siteTreeSpecies->site_id = $site->id;
                $siteTreeSpecies->name = $species['name'];
                if (isset($data['site_submission_id'])) {
                    $siteTreeSpecies->site_submission_id = $data['site_submission_id'];
                    $siteTreeSpecies->amount = data_get($species, 'amount', 0);
                }
                $siteTreeSpecies->saveOrFail();
            }
        }

        $site->refresh();

        return JsonResponseHelper::success(new SiteResource($site), 200);
    }

    public function deleteAction(SiteTreeSpecies $siteTreeSpecies): JsonResponse
    {
        $this->authorize('update', $siteTreeSpecies->site);

        $siteTreeSpecies->delete();

        return JsonResponseHelper::success([], 200);
    }

    public function readAllAction(Request $request): JsonResponse
    {
        $this->authorize('readAllTreeSpecies', Site::class);

        $sites = Site::whereIn('programme_id', Auth::user()->programmes->pluck('id'))->get();

        $resources = [];
        foreach ($sites as $site) {
            $treeSpecies = [];
            foreach ($site->siteTreeSpecies->unique('name') as $siteTreeSpecies) {
                $treeSpecies[] = new BaseSiteTreeSpeciesResource($siteTreeSpecies);
            }
            if (! empty($treeSpecies)) {
                $resources[] = new AllSiteTreeSpeciesResource($treeSpecies);
            }
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAllBySiteAction(Site $site): JsonResponse
    {
        $this->authorize('read', $site);

        $resources = [];
        $siteOnly = $site->siteTreeSpecies()->whereNull('site_submission_id')->get();
        foreach ($siteOnly as $siteTreeSpecies) {
            $resources[] = new SiteTreeSpeciesResource($siteTreeSpecies);
        }

        return JsonResponseHelper::success($resources, 200);
    }
}
