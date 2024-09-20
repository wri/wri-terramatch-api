<?php

namespace App\Http\Controllers\V2\Exports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Http\Resources\V2\User\UserLiteResource;
use App\Models\V2\User;

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
        $user =  new UserLiteResource(User::find($media->created_by));
        $createdBy = $user->first_name . ' ' . $user->last_name;
        $metadata = [
          'name' => $media->name,
          'is_cover' => $media->is_cover ? 'true' : 'false',
          'is_public' => $media->is_public ? 'true' : 'false',
          'photographer' => $media->photographer, 
          'description' => $media->description,
          'uploaded_by' => $media->uploaded_by,
          'DateCaptured' => $media->date_captured,
          'filename' => $media->file_name,
          'geotagged' => ($media->lat !== null && $media->lng !== null) ? 'true' : 'false',
          'coordinates' => json_encode([
              'lat' => $media->lat,
              'lng' => $media->lng
          ]),
          'created_by' => $createdBy
        ];
        
        $process = new Process([
            'exiftool',
            '-name=' . escapeshellarg($metadata['name']),
            '-is_cover=' . escapeshellarg($metadata['is_cover']),
            '-is_public=' . escapeshellarg($metadata['is_public']),
            '-photographer=' . escapeshellarg($metadata['photographer']),
            '-description=' . escapeshellarg($metadata['description']),
            '-data_captured=' . escapeshellarg($metadata['DateCaptured']),
            '-file_name=' . escapeshellarg($metadata['filename']),
            '-coordinates=' . escapeshellarg($media->lat),
            '-geotagged=' . escapeshellarg($metadata['geotagged']),
            '-created_by=' . escapeshellarg($metadata['created_by']),
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
