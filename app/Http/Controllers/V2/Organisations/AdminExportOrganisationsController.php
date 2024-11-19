<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\ExportAllOrganisationsJob;
use App\Models\DelayedJob;
use App\Models\V2\Organisation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class AdminExportOrganisationsController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('export', Organisation::class);

        $filename = 'organisations(' . now()->format('d-m-Y'). ').csv';
        $relativePath = 'exports/' . $filename;
        $absolutePath = storage_path('app/' . $relativePath);

        try {
            $binary_data = Redis::get('exports:organisations:'.$filename);
            if (! $binary_data) {
                $delayedJob = DelayedJob::create();
                $job = new ExportAllOrganisationsJob(
                    $delayedJob->id,
                    $filename
                );
                dispatch($job);

                return (new DelayedJobResource($delayedJob))->additional(['message' => "Export for organisations $filename is being processed"]);
            } else {
                file_put_contents($absolutePath, $binary_data);

                return response()->download($absolutePath, $filename)->deleteFileAfterSend(true);
            }
        } catch (\Exception $e) {
            Log::error('Error during export for organisations : ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during organisations export'], 500);
        }
    }
}
