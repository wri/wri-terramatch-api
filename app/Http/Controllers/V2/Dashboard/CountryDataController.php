<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\SitePolygon;
use App\Services\PolygonService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class CountryDataController extends Controller
{
    public function getCountryBbox(string $iso)
    {
        $countryBbox = App::make(PolygonService::class)->getCountryBbox($iso);

        return response()->json(['bbox' => $countryBbox]);
    }

    public function getPolygonData(string $uuid)
    {
        $sitePolygon = SitePolygon::where('poly_id', $uuid)->first();

        if (! $sitePolygon) {
            return response()->json(['error' => 'Polygon not found'], 404);
        }

        $project = $sitePolygon->project()->first();

        if (! $project) {
            Log::error("Project not found for site polygon with ID: $sitePolygon->id");
        }

        $site = $sitePolygon->site()->first();

        if (! $site) {
            Log::error("Site not found for site polygon with ID: $sitePolygon->id");

        }

        $data = [
          ['key' => 'poly_name', 'title' => 'title', 'value' => $sitePolygon->poly_name ?? null],
          ['key' => 'project_name', 'title' => 'Project', 'value' => $project->name ?? null],
          ['key' => 'site_name', 'title' => 'Site', 'value' => $site?->name ?? null],
          ['key' => 'num_trees', 'title' => 'Number of trees', 'value' => $sitePolygon->num_trees ?? null],
          ['key' => 'plantstart', 'title' => 'Plant Start Date', 'value' => $sitePolygon->plantstart ?? null],
          ['key' => 'status', 'title' => 'Status', 'value' => $sitePolygon->status ?? null],

        ];

        return response()->json(['data' => $data]);
    }

    public function getProjectData(string $uuid)
    {
        try {
            $project = Project::isUuid($uuid)->first();

            if (! $project) {
                Log::error("Project not found for project with UUID: $uuid");

                return response()->json(['error' => 'Project not found'], 404);
            }

            $organization = $project->organisation()->first();
            if (! $organization) {
                Log::error("Organization not found for project with ID: $project->id");
            }

            $data = [
              ['key' => 'project_name', 'title' => 'title', 'value' => $project->name],
              ['key' => 'organizations', 'title' => 'Organization', 'value' => $organization?->name],
              ['key' => 'total_hectares_restored', 'title' => 'Total Hectares Restored', 'value' => round($project->total_hectares_restored_sum, 2)],
            ];

            return response()->json(['data' => $data]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching project data', 'message' => $e->getMessage()], 500);
        }

    }
}
