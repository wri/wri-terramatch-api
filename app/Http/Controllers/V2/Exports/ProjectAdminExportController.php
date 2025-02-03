<?php

namespace App\Http\Controllers\V2\Exports;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\V2\GenerateProjectAdminEntityExportJob;
use App\Models\DelayedJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class ProjectAdminExportController extends Controller
{
    public function __invoke(Request $request, string $entity, string $framework)
    {
        $user = Auth::user();
        $key = 'export:project-manager|'.$user->uuid.'|'.$entity.'|'.$framework;
        try {
            $cacheValue = Redis::get($key);

            if (! $cacheValue) {
                $delayedJob = DelayedJob::create();
                $job = new GenerateProjectAdminEntityExportJob(
                    $delayedJob->id,
                    $user->uuid,
                    $entity,
                    $framework
                );
                dispatch($job);

                return (new DelayedJobResource($delayedJob))->additional(['message' => 'Exports are being generated.']);
            } else {

                $fileKey = $cacheValue;
                Redis::del($key);

                $expiration = now()->addMinutes(60);

                $presignedUrl = Storage::disk('s3')->temporaryUrl($fileKey, $expiration);

                return response()->json(['url' => $presignedUrl]);
            }
        } catch (\Exception $e) {
            Log::error('Error during active-countries-table : ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during pm projects export'], 500);
        }
    }
}
