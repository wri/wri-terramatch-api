<?php

namespace App\Http\Controllers\V2\ReportingFrameworks;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ReportingFrameworks\ReportingFrameworkResource;
use App\Models\Framework;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ViewReportingFrameworkViaAccessCodeController extends Controller
{
    public function __invoke(Request $request, string $accessCode)
    {
        $framework = Framework::where('access_code', $accessCode)->first();
        if ($framework == null) return new JsonResponse('No reporting framework found', 404);

        return new ReportingFrameworkResource($framework);
    }
}
