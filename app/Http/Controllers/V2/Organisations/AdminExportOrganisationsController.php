<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Exports\V2\OrganisationsExport;
use App\Http\Controllers\Controller;
use App\Models\V2\Organisation;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Http\Resources\DelayedJobResource;
use App\Models\DelayedJob;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Jobs\ExportAllOrganisationsJob;


class AdminExportOrganisationsController extends Controller
{
    public function __invoke(Request $request)
    {
        $this->authorize('export', Organisation::class);

        $filename = 'organisations(' . now()->format('d-m-Y-H-i'). ').csv';

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
                file_put_contents($filename, $binary_data);

                return response();
            }
        } catch (\Exception $e) {
            Log::error('Error during export for organisations : ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during organisations export'], 500);
        }

        // return (new OrganisationsExport())->download($filename, Excel::CSV)->deleteFileAfterSend(true);
    }
}
