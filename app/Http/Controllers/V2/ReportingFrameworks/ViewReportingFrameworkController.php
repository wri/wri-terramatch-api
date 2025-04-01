<?php

namespace App\Http\Controllers\V2\ReportingFrameworks;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ReportingFrameworks\ReportingFrameworkResource;
use App\Models\Framework;
use Illuminate\Http\Request;

class ViewReportingFrameworkController extends Controller
{
    public function __invoke(Request $request, string $frameworkKey): ReportingFrameworkResource
    {
        $framework = Framework::where('slug', $frameworkKey)->firstOrFail();

        return new ReportingFrameworkResource($framework);
    }
}
