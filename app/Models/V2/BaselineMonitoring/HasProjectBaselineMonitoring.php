<?php
namespace App\Models\V2\BaselineMonitoring;

 trait HasProjectBaselineMonitoring
 {
     public function baselineMonitoring()
     {
         return $this->morphMany(ProjectMetric::class, 'monitorable')
             ->isStatus(ProjectMetric::STATUS_ACTIVE);
     }

     public function baselineMonitoringHistoric()
     {
         return $this->morphMany(ProjectMetric::class, 'monitorable')
             ->isStatus(ProjectMetric::STATUS_ARCHIVED);
     }
 }