<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (! empty($media)) {
            $permission = empty($collection) ? 'deleteMedia' : 'delete' . ucfirst($collection) .'Media';
            $this->authorize($permission, $model);
        }
        Media::find($media->id)->delete();

        return response()->json(['success' => 'media has been deleted'], 202);
    }

    public function bulkDelete(Request $request): JsonResponse
    {
        if (!Auth::user()->can('media-manage')) {
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
}
