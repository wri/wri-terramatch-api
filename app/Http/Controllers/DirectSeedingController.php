<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Http\Requests\StoreDirectSeedingRequest;
use App\Models\DirectSeeding;
use App\Models\SiteSubmission;
use App\Resources\DirectSeedingResource;
use Illuminate\Http\JsonResponse;

class DirectSeedingController extends Controller
{
    public function createAction(StoreDirectSeedingRequest $request, SiteSubmission $siteSubmission = null): JsonResponse
    {
        $data = $request->json()->all();
        if (is_null($siteSubmission)) {
            $siteSubmission = SiteSubmission::where('id', $data['site_submission_id'])->firstOrFail();
            unset($data['site_submission_id']);
        }
        $this->authorize('update', $siteSubmission);

        $extra = [
            'site_submission_id' => $siteSubmission->id,
        ];

        $directSeeding = DirectSeeding::create(array_merge($data, $extra));

        return JsonResponseHelper::success(new DirectSeedingResource($directSeeding), 201);
    }

    public function deleteAction(DirectSeeding $directSeeding): JsonResponse
    {
        $this->authorize('delete', $directSeeding->siteSubmission);
        $directSeeding->delete();

        return JsonResponseHelper::success([], 200);
    }
}
