<?php

namespace App\Http\Controllers\V2\Exports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GeneratePreSignedURLDownloadReportController extends Controller
{
    public function __invoke(Request $request, string $entity, string $framework)
    {
        $fileKey = 'exports/all-entity-records/'.$entity.'-'.$framework.'.csv';

        $expiration = now()->addMinutes(60);

        $presignedUrl = Storage::disk('s3')->temporaryUrl($fileKey, $expiration);

        return response()->json(['url' => $presignedUrl]);
    }
}
