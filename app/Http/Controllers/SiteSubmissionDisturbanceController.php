<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Models\SiteSubmission;
use App\Models\SiteSubmissionDisturbance;
use App\Resources\SiteSubmissionDisturbanceResource;
use App\Resources\SiteSubmissionResource;
use App\Validators\SiteDisturbanceValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteSubmissionDisturbanceController extends Controller
{
    public function createAction(Request $request): JsonResponse
    {
        $data = $request->json()->all();
        SiteDisturbanceValidator::validate('CREATE', $data);
        $submission = SiteSubmission::where('id', $data['site_submission_id'])->firstOrFail();
        $this->authorize('update', $submission->site);

        $disturbance = new SiteSubmissionDisturbance();
        $disturbance->site_submission_id = $data['site_submission_id'];
        $disturbance->disturbance_type = $data['disturbance_type'];
        $disturbance->extent = $data['extent'];
        $disturbance->intensity = $data['intensity'];
        if (isset($data['description'])) {
            $disturbance->description = $data['description'];
        }
        $disturbance->saveOrFail();

        return JsonResponseHelper::success(new SiteSubmissionDisturbanceResource($disturbance), 201);
    }

    public function updateAction(SiteSubmissionDisturbance $siteSubmissionDisturbance, Request $request): JsonResponse
    {
        $data = $request->json()->all();
        SiteDisturbanceValidator::validate('UPDATE', $data);
        $this->authorize('update', $siteSubmissionDisturbance);

        $siteSubmissionDisturbance->fill($data);
        $siteSubmissionDisturbance->saveOrFail();

        return JsonResponseHelper::success(new SiteSubmissionDisturbanceResource($siteSubmissionDisturbance), 200);
    }

    public function deleteAction(SiteSubmissionDisturbance $siteSubmissionDisturbance, Request $request): JsonResponse
    {
        $this->authorize('delete', $siteSubmissionDisturbance);

        $siteSubmissionDisturbance->delete();

        return JsonResponseHelper::success((object) [], 200);
    }

    public function createDisturbanceInformationAction(Request $request): JsonResponse
    {
        $data = $request->json()->all();
        SiteDisturbanceValidator::validate('CREATE_DISTURBANCE_INFORMATION', $data);
        $submission = SiteSubmission::where('id', $data['site_submission_id'])->firstOrFail();
        $this->authorize('update', $submission->site);

        $submission->disturbance_information = $data['disturbance_information'];
        $submission->saveOrFail();

        return JsonResponseHelper::success(new SiteSubmissionResource($submission), 200);
    }

    public function updateDisturbanceInformationAction(SiteSubmission $siteSubmission, Request $request): JsonResponse
    {
        $data = $request->json()->all();
        SiteDisturbanceValidator::validate('UPDATE_DISTURBANCE_INFORMATION', $data);
        $this->authorize('update', $siteSubmission->site);

        $siteSubmission->disturbance_information = $data['disturbance_information'];
        $siteSubmission->saveOrFail();

        return JsonResponseHelper::success(new SiteSubmissionResource($siteSubmission), 200);
    }

    public function deleteDisturbanceInformationAction(SiteSubmission $siteSubmission): JsonResponse
    {
        $this->authorize('update', $siteSubmission->site);

        $siteSubmission->disturbance_information = null;
        $siteSubmission->saveOrFail();

        return JsonResponseHelper::success(new SiteSubmissionResource($siteSubmission), 200);
    }
}
