<?php

namespace App\Http\Controllers;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Requests\StoreMediaUploadRequest;
use App\Models\MediaUpload;
use App\Resources\MediaUploadResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MediaUploadController extends Controller
{
    public function createAction(StoreMediaUploadRequest $request): JsonResponse
    {
        $this->authorize('create', MediaUpload::class);
        $data = $request->json()->all();

        $me = Auth::user();
        $data['upload'] = UploadHelper::findByIdAndValidate(
            $data['upload'],
            UploadHelper::IMAGES_VIDEOS,
            $me->id
        );

        $media = new MediaUpload();
        $media->is_public = $data['is_public'];
        $media->upload = $data['upload'];
        if (isset($data['location_long']) && ! is_null($data['location_long'])) {
            $media->location_long = $data['location_long'];
        }
        if (isset($data['location_lat']) && ! is_null($data['location_lat'])) {
            $media->location_lat = $data['location_lat'];
        }
        if (isset($data['programme_id'])) {
            $media->programme_id = $data['programme_id'];
        } else {
            $media->site_id = $data['site_id'];
            if (isset($data['site_submission_id'])) {
                $media->site_submission_id = $data['site_submission_id'];
            }
        }
        $media->saveOrFail();

        return JsonResponseHelper::success(new MediaUploadResource($media), 201);
    }

    public function deleteAction(MediaUpload $mediaUpload): JsonResponse
    {
        $this->authorize('delete', $mediaUpload);

        $mediaUpload->delete();

        return JsonResponseHelper::success((object) [], 200);
    }
}
