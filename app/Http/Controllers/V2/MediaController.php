<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

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
}
