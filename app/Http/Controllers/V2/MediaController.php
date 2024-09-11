<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Files\FileResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaController extends Controller
{
    public function delete(Request $request, string $uuid, string $collection = ''): JsonResponse
    {
        $qry = Media::where('uuid', $uuid);
        if (! empty($collection)) {
            $qry->where('collection_name', $collection);
        }
        $media = $qry->first();

        if (empty($media)) {
            throw new ModelNotFoundException();
        }

        $model = $media->model;

        $permission = empty($collection) ? 'deleteMedia' : 'delete' . ucfirst($collection) .'Media';
        $this->authorize($permission, $model);

        $media->delete();

        return response()->json(['success' => 'media has been deleted'], 202);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        if (! Auth::user()->can('media-manage')) {
            throw new AuthorizationException('No permission to bulk delete');
        }

        $uuids = $request->input('uuids');
        if (empty($uuids)) {
            throw new NotFoundHttpException();
        }

        $media = Media::whereIn('uuid', $uuids)->where('created_by', Auth::user()->id);
        if ($media->count() != count($uuids)) {
            // If the bulk delete endpoint is being called for some media that weren't created by this user,
            // avoid deleting any of them.
            throw new AuthorizationException('Some of the media you are trying to delete were not created by you.');
        }

        $media->delete();

        return response()->json(['success' => 'media has been deleted'], 202);
    }

    public function updateMedia(Request $request, string $uuid): JsonResponse
    {
        $media = Media::where('uuid', $uuid)->first();

        DB::transaction(function () use ($media, $request) {
            $updateData = [];

            if ($request->has('description')) {
                $updateData['description'] = $request->input('description');
            }

            if ($request->has('photographer')) {
                $updateData['photographer'] = $request->input('photographer');
            }

            if ($request->has('is_public')) {
                $updateData['is_public'] = $request->input('is_public');
            }

            if (! empty($updateData)) {
                $media->update($updateData);
            }

            if ($request->has('is_cover') && $request->input('is_cover')) {
                Media::where('model_type', $media->model_type)
                    ->where('model_id', $media->model_id)
                    ->where('id', '!=', $media->id)
                    ->update(['is_cover' => false]);

                $media->is_cover = true;
                $media->save();
            }
        });

        return response()->json(new FileResource($media), 200);
    }
}
