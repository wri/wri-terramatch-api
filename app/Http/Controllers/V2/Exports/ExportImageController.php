<?php

namespace App\Http\Controllers\V2\Exports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ExportImageController extends Controller
{
    public function __invoke(Request $request)
    {
        $imageUuid = $request->input('uuid');
        $media = Media::where('uuid', $imageUuid)->firstOrFail();
        $imageUrl = $media->getFullUrl();
        $imageContent = Http::get($imageUrl)->body();

        $tempImagePath = storage_path('app/temp_image.jpg');
        file_put_contents($tempImagePath, $imageContent);

        $metadata = [
            'Title' => $media->name,
            'Comment' => $media->description,
            'Author' => $media->photographer,
            'Latitude' => $media->lat,
            'Longitude' => $media->lng,
            'CreateDate' => $media->created_at->toDateTimeString(),
        ];

        $process = new Process([
            'exiftool',
            '-Title=' . $metadata['Title'],
            '-Comment=' . $metadata['Comment'],
            '-Author=' . $metadata['Author'],
            '-GPSLatitude=' . $metadata['Latitude'],
            '-GPSLongitude=' . $metadata['Longitude'],
            '-CreateDate=' . $metadata['CreateDate'],
            $tempImagePath,
        ]);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $updatedImageContent = file_get_contents($tempImagePath);

        return response($updatedImageContent)
            ->header('Content-Type', 'image/jpeg')
            ->header('Content-Disposition', 'attachment; filename="' . $media->file_name . '"');
    }
}
