<?php

namespace App\Http\Controllers\V2\Exports;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\User\UserLiteResource;
use App\Models\V2\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);

        $tempDir = sys_get_temp_dir();
        $tempImagePath = $tempDir . DIRECTORY_SEPARATOR . uniqid('image_', true) . '.' . $extension;

        file_put_contents($tempImagePath, $imageContent);
        $user = new UserLiteResource(User::find($media->created_by));
        $createdBy = $user ? $user->first_name . ' ' . $user->last_name : 'Unkonwn';

        $metadata = [
          'XMP-dc:Title=' . escapeshellarg($media->name),
          'XMP-dc:Creator=' . escapeshellarg($createdBy),
          'XMP-dc:Description=' . escapeshellarg($media->description ?? ''),
          'DateTimeOriginal=' . escapeshellarg($media->created_at ?? ''),
          'Artist=' . escapeshellarg($media->photographer ?? ''),
        ];

        if ($media->lat !== null && $media->lng !== null) {
            $metadata[] = 'GPSLatitude=' . escapeshellarg($media->lat);
            $metadata[] = 'GPSLongitude=' . escapeshellarg($media->lng);
        }

        $userComment = json_encode([
            'IsCover' => $media->is_cover ? 'true' : 'false',
            'IsPublic' => $media->is_public ? 'true' : 'false',
            'UploadedBy' => $createdBy,
            'Filename' => $media->file_name,
            'Geotagged' => ($media->lat !== null && $media->lng !== null) ? 'true' : 'false',
            'Name' => $media->name,
            'Photographer' => $media->photographer ?? '',
            'Description' => $media->description ?? '',
            'Date Captured' => $media->created_at,
            'Coordinates' => [
                'Latitude' => $media->lat,
                'Longitude' => $media->lng,
            ],
        ]);

        $metadata[] = 'UserComment=' . escapeshellarg($userComment);

        $command = ['exiftool', '-overwrite_original'];
        foreach ($metadata as $tag) {
            $command[] = '-' . $tag;
        }
        $command[] = $tempImagePath;

        try {
            $process = new Process($command);
            $process->run();
            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $updatedImageContent = file_get_contents($tempImagePath);

            unlink($tempImagePath);

            return response($updatedImageContent)
                ->header('Content-Type', 'image/' . $extension)
                ->header('Content-Disposition', 'attachment; filename="' . $media->file_name . '"');
        } catch (\Exception $e) {
            Log::error('Error updating metadata: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred while processing the image'], 500);
        }
    }
}
