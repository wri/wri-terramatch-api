<?php

namespace App\Resources;

use App\Models\SatelliteMap as SatelliteMapModel;

class SatelliteMapResource extends Resource
{
    public function __construct(SatelliteMapModel $satelliteMap)
    {
        $this->id = $satelliteMap->id;
        $this->monitoring_id = $satelliteMap->monitoring_id;
        /**
         * This property is used as a temporary flag to indicate whether the
         * ConvertSatelliteMapJob has run or not. Users can only upload TIFFs,
         * therefore the extension will be .tiff until the job has finished. If
         * it's not been converted yet we should show nothing (as TIFFs cannot
         * be rendered by browsers).
         */
        $hasJobFinished = explode_pop('.', $satelliteMap->map) != 'tiff';
        $this->map = $hasJobFinished ? $satelliteMap->map : null;
        $this->alt_text = $satelliteMap->alt_text;
        $this->created_at = $satelliteMap->created_at;
        $this->created_by = $satelliteMap->created_by;
    }
}
