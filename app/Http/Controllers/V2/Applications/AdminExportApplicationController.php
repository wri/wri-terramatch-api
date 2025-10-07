<?php

namespace App\Http\Controllers\V2\Applications;

use App\Http\Controllers\Controller;
use App\Models\V2\Forms\Application;
use App\Models\V2\FundingProgramme;
use App\Models\V2\SavedExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminExportApplicationController extends Controller
{
    public function __invoke(Request $request, FundingProgramme $fundingProgramme)
    {
        $this->authorize('exportAll', Application::class);

        $savedExport = SavedExport::where('funding_programme_id', $fundingProgramme->id)->latest()->firstOrFail();

        $presignedUrl = Storage::disk('s3')->temporaryUrl($savedExport->name, now()->addMinutes(60));

        return response()->json(['url' => $presignedUrl]);
    }
}
