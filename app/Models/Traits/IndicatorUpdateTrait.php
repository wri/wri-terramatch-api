<?php

namespace App\Models\Traits;

use App\Services\IndicatorUpdateService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

trait IndicatorUpdateTrait
{
    /**
     * Update indicators for a polygon
     *
     * @param string $polygonUuid The UUID of the polygon
     * @return bool Whether the update was successful
     */
    protected function updateIndicatorsForPolygon(string $polygonUuid): bool
    {
        try {
            $indicatorService = App::make(IndicatorUpdateService::class);
            Log::info("Updating indicators for polygon {$polygonUuid}");
            $results = $indicatorService->updateIndicatorsForPolygon($polygonUuid);

            $success = true;
            foreach ($results as $slug => $result) {
                if ($result['status'] === 'error') {
                    Log::warning("Failed to update indicator {$slug} for polygon {$polygonUuid}: {$result['message']}");
                    $success = false;
                }
            }

            return $success;
        } catch (\Exception $e) {
            Log::error("Error updating indicators for polygon {$polygonUuid}: {$e->getMessage()}");

            return false;
        }
    }
}
