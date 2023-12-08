<?php

namespace App\Resources;

use App\Models\SatelliteMonitor as SatelliteMonitorModel;

class SatelliteMonitorResource extends Resource
{
    public function __construct(SatelliteMonitorModel $satelliteMonitor)
    {
        $this->id = $satelliteMonitor->id;
        $this->satellite_monitorable_type = $satelliteMonitor->satellite_monitorable_type;
        $this->satellite_monitorable_id = $satelliteMonitor->satellite_monitorable_id;
        /**
         * This property is used as a temporary flag to indicate whether the
         * ConvertSatelliteMapJob has run or not. Users can only upload TIFFs,
         * therefore the extension will be .tiff until the job has finished. If
         * it's not been converted yet we should show nothing (as TIFFs cannot
         * be rendered by browsers).
         */
        $hasJobFinished = explode_pop('.', $satelliteMonitor->map) != 'tiff';
        $this->map = $hasJobFinished ? $satelliteMonitor->map : null;
        $this->alt_text = $satelliteMonitor->alt_text;
        $this->created_at = $satelliteMonitor->created_at;
    }
}
