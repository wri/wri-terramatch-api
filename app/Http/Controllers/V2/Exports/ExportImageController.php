<?php

namespace App\Http\Controllers\V2\Exports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Intervention\Image\ImageManagerStatic as Image;
use App\Models\V2\User;

class ExportImageController extends Controller
{
    public function __invoke(Request $request)
    {
        $imageUuid = $request->input('uuid');
        $media = Media::where('uuid', $imageUuid)->firstOrFail();
        $imageUrl = $media->getFullUrl();
        $imageContent = Http::get($imageUrl)->body();

        $image = Image::make($imageContent);

        $user = User::find($media->model_id);
        $userName = $user ? $user->name : 'Unknown';

        $metadata = [
            'name' => $media->name,
            'is_cover' => $media->is_cover ? true : false,
            'is_public' => $media->is_public ? true : false,
            'photographer' => $media->photographer,
            'description' => $media->description,
            'created_by' => $userName,
            'created_at' => $media->created_at->toDateTimeString(),
            'filename' => $media->file_name,
            'geotagged' => ($media->lat !== null && $media->lng !== null) ? true : false,
            'coordinates' => [
                'lat' => $media->lat,
                'lng' => $media->lng
            ]
        ];

        foreach ($metadata as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif (is_array($value)) {
                $value = json_encode($value);
            }
            $image->exif($key, $value);
        }

        $modifiedImageContent = $image->encode('jpg')->getEncoded();

        return response($modifiedImageContent)
            ->header('Content-Type', 'image/jpeg')
            ->header('Content-Disposition', 'attachment; filename="' . $media->file_name . '"');
    }
}