<?php

namespace App\Http\Controllers\V2\ReportingFrameworks;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ReportingFrameworks\ReportingFrameworkResource;
use App\Models\Framework;
use Illuminate\Http\Request;

class ViewReportingFrameworkViaAccessCodeController extends Controller
{
    public function __invoke(Request $request, string $accessCode): ReportingFrameworkResource
    {
        $framework = Framework::where('access_code', $accessCode)->first();

        return new ReportingFrameworkResource($framework);
    }
}
