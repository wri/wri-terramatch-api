<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Helpers\GeometryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use Illuminate\Support\Facades\Log;

class TerrafundPointsController extends Controller
{
  public function calculateCentroidOfCentroids(string $projectUuid)
  {
    $geometryHelper = new GeometryHelper(); 
    $centroid = $geometryHelper->centroidOfProject($projectUuid);
    return $centroid;
  }
  public function updateProjectCentroids()
  {
      $geometryHelper = new GeometryHelper(); // Instantiate GeometryHelper
  
      $projectUuids = Project::distinct()->pluck('uuid');
  
      foreach ($projectUuids as $projectUuid) {
          $centroid = $geometryHelper->centroidOfProject($projectUuid);
  
          if ($centroid === null) {
              Log::warning("Invalid centroid for projectUuid: $projectUuid");
              continue; 
          }
  
          $centroidArray = json_decode($centroid, true);

          $latitude = $centroidArray['coordinates'][1];
          $longitude = $centroidArray['coordinates'][0];
  
          
          Project::where('uuid', $projectUuid)
              ->update([
                  'lat' => $latitude,
                  'long' => $longitude,
              ]);
  
          
          Log::info("Centroid updated for projectUuid: $projectUuid");
      }
  
      return "Centroids updated successfully!";
  }

}
