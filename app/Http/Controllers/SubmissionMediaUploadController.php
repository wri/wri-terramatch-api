<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreSubmissionMediaUploadRequest;
use App\Models\SubmissionMediaUpload;
use App\Resources\SubmissionMediaUploadResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SubmissionMediaUploadController extends Controller
{
    public function createAction(StoreSubmissionMediaUploadRequest $request): JsonResponse
    {
        $this->authorize('create', SubmissionMediaUpload::class);
        $data = $request->json()->all();

        $me = Auth::user();
        $data['upload'] = UploadHelper::findByIdAndValidate(
            $data['upload'],
            UploadHelper::IMAGES_VIDEOS,
            $me->id
        );

        $media = new SubmissionMediaUpload();
        $media->is_public = $data['is_public'];
        $media->upload = $data['upload'];
        if (isset($data['location_long']) && ! is_null($data['location_long'])) {
            $media->location_long = $data['location_long'];
        }
        if (isset($data['location_lat']) && ! is_null($data['location_lat'])) {
            $media->location_lat = $data['location_lat'];
        }
        if (isset($data['submission_id'])) {
            $media->submission_id = $data['submission_id'];
        } else {
            $media->site_submission_id = $data['site_submission_id'];
        }
        $media->saveOrFail();

        return JsonResponseHelper::success(new SubmissionMediaUploadResource($media), 201);
    }

    public function deleteAction(SubmissionMediaUpload $submissionMediaUpload): JsonResponse
    {
        $this->authorize('delete', $submissionMediaUpload);

        $submissionMediaUpload->delete();

        return JsonResponseHelper::success((object) [], 200);
    }

    public function downloadTemplateAction(Request $request): BinaryFileResponse
    {
        $this->authorize('download', SubmissionMediaUpload::class);

        $filename = 'terra-match-project-pitch-funding-offer-checklist.pdf';
        $path = base_path('resources/documentation/terra-match-project-pitch-funding-offer-checklist.pdf');
        $headers = [
            'Content-Type' => 'application/xlsx',
        ];

        return response()->download($path, $filename, $headers);
    }
}
