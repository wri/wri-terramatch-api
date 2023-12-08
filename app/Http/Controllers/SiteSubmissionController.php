<?php

namespace App\Http\Controllers;

use App\Helpers\ControllerHelper;
use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StoreDocumentFileRequest;
use App\Http\Requests\StoreSiteSubmissionRequest;
use App\Http\Requests\UpdateSiteSubmissionRequest;
use App\Models\Site;
use App\Models\SiteSubmission;
use App\Resources\SiteSubmissionResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class SiteSubmissionController extends Controller
{
    public function createAction(StoreSiteSubmissionRequest $request): JsonResponse
    {
        $data = $request->json()->all();

        $site = Site::where('id', $data['site_id'])->firstOrFail();
        $this->authorize('create', $site);

        $siteSubmission = new SiteSubmission($data);
        $siteSubmission->saveOrFail();

        return JsonResponseHelper::success(new SiteSubmissionResource($siteSubmission), 201);
    }

    public function updateAction(UpdateSiteSubmissionRequest $request, SiteSubmission $siteSubmission): JsonResponse
    {
        $this->authorize('update', $siteSubmission);
        $data = $request->all();

        if (isset($data['additional_tree_species'])) {
            $existingFile = $siteSubmission->getDocumentFileCollection(['tree_species'])->first();
            if (! empty($existingFile) && $existingFile->id != $data['additional_tree_species']) {
                $existingFile->delete();
            }

            ControllerHelper::callAction('DocumentFileController@createAction', [
                'document_fileable_id' => $siteSubmission->id,
                'document_fileable_type' => 'site_submission',
                'upload' => $data['additional_tree_species'],
                'is_public' => false,
                'title' => 'Additional Tree Species',
                'collection' => 'tree_species',
            ], new StoreDocumentFileRequest());
        }

        $siteSubmission->fill(Arr::except($data, ['additional_tree_species']));
        $siteSubmission->saveOrFail();

        return JsonResponseHelper::success(new SiteSubmissionResource($siteSubmission), 200);
    }

    public function readAllBySiteAction(Site $site): JsonResponse
    {
        $this->authorize('read', $site);

        $siteSubmissions = SiteSubmission::where('site_id', $site->id)->get();

        $resources = [];
        foreach ($siteSubmissions as $siteSubmission) {
            $resources[] = new SiteSubmissionResource($siteSubmission);
        }

        return JsonResponseHelper::success($resources, 200);
    }

    public function readAction(SiteSubmission $siteSubmission): JsonResponse
    {
        $this->authorize('read', $siteSubmission->site);

        return JsonResponseHelper::success(new SiteSubmissionResource($siteSubmission), 200);
    }

    public function approveAction(Request $request, SiteSubmission $siteSubmission): JsonResponse
    {
        $this->authorize('approve', $siteSubmission);

        $siteSubmission->approved_by = Auth::user()->id;
        $siteSubmission->approved_at = Carbon::now();
        $siteSubmission->saveOrFail();

        return JsonResponseHelper::success(new SiteSubmissionResource($siteSubmission), 200);
    }
}
