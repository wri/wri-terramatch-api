<?php

namespace App\Http\Controllers\Terrafund;

use App\Helpers\JsonResponseHelper;
use App\Helpers\UploadHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Terrafund\StoreBulkTerrafundFileRequest;
use App\Http\Requests\Terrafund\StoreTerrafundFileRequest;
use App\Models\Terrafund\TerrafundFile;
use App\Resources\Terrafund\TerrafundFileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TerrafundFileController extends Controller
{
    public function createAction(StoreTerrafundFileRequest $request): JsonResponse
    {
        $data = $request->json()->all();

        $fileable = getTerrafundModelDataFromMorphable($data['fileable_type'], $data['fileable_id']);
        $this->authorize('createFile', $fileable['model']);

        if (isset($data['collection'])) {
            $fileable['files'] = $fileable['files'][$data['collection']];
        }

        $me = Auth::user();
        $data['fileable_type'] = get_class($fileable['model']);
        $data['upload'] = UploadHelper::findByIdAndValidate(
            $data['upload'],
            $fileable['files'],
            $me->id
        );

        $terrafundFile = TerrafundFile::create($data);

        return JsonResponseHelper::success(new TerrafundFileResource($terrafundFile), 201);
    }

    public function deleteAction(TerrafundFile $terrafundFile): JsonResponse
    {
        $this->authorize('deleteFile', $terrafundFile->fileable);

        $terrafundFile->delete();

        return JsonResponseHelper::success((object) [], 200);
    }

    public function bulkCreateAction(StoreBulkTerrafundFileRequest $request)
    {
        $data = $request->json()->all();
        $me = Auth::user();
        $resources = [];

        foreach ($data['data'] as $file) {
            $fileable = getTerrafundModelDataFromMorphable($file['fileable_type'], $file['fileable_id']);

            $this->authorize('createFile', $fileable['model']);

            $file['upload'] = UploadHelper::findByIdAndValidate(
                $file['upload'],
                $fileable['files'],
                $me->id
            );

            $extra = [
                'fileable_type' => get_class($fileable['model']),
                'location_long' => $data['location_long'] ?? null,
                'location_lat' => $data['location_lat'] ?? null,
            ];

            $terrafundFile = TerrafundFile::create(array_merge($file, $extra));

            $resources[] = new TerrafundFileResource($terrafundFile);
        }

        return JsonResponseHelper::success($resources, 201);
    }
}
