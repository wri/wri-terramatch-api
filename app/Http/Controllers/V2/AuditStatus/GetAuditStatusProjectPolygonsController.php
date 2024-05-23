<?php

namespace App\Http\Controllers\V2\AuditStatus;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\AuditStatusProjectPolygonsResource;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;

class GetAuditStatusProjectPolygonsController extends Controller
{
    public function __invoke(Request $request, string $uuid = null)
    {
        $project = Project::where('uuid', $uuid)->with('sitePolygons')->get();
        $sitePolygons = $project->pluck('sitePolygons')->flatten();
        return AuditStatusProjectPolygonsResource::collection($sitePolygons);
    }
}
