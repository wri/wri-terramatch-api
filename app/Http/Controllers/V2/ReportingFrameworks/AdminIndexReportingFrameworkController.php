<?php

namespace App\Http\Controllers\V2\ReportingFrameworks;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\ReportingFrameworks\ReportingFrameworkResource;
use App\Models\Framework;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AdminIndexReportingFrameworkController extends Controller
{
    public function __invoke(Request $request): ResourceCollection
    {
        $frameworks = Framework::all();

        return ReportingFrameworkResource::collection($frameworks);
    }
}
