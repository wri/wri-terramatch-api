<?php

namespace App\Http\Controllers\V2\Files;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\File\UpdateFilePropertiesRequest;
use App\Http\Resources\V2\Files\FileResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FilePropertiesController extends Controller
{
    public function update(string $uuid, UpdateFilePropertiesRequest $request)
    {
        $media = Media::where('uuid', $uuid)->first();

        if (empty($media)) {
            throw new ModelNotFoundException();
        }

        $model = $media->model()->first();

        $this->authorize('updateFileProperties', $model);

        $data = $request->all();

        $media->name = data_get($data, 'title');
        $media->is_public = data_get($data, 'is_public', false);
        $media->save();

        return new FileResource($media);
    }

    public function destroy(string $uuid, Request $request)
    {
        $media = Media::where('uuid', $uuid)->first();

        if (empty($media)) {
            throw new ModelNotFoundException();
        }

        $model = $media->model()->first();

        $this->authorize('deleteFiles', $model);

        $media->delete();

        return JsonResponseHelper::success(['uuid' => $uuid ], 200);
    }
}
