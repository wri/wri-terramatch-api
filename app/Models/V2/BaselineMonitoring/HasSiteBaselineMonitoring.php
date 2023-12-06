<?php
namespace App\Models\V2\BaselineMonitoring;

 trait HasSiteBaselineMonitoring
 {
     public function baselineMonitoring()
     {
         return $this->morphMany(SiteMetric::class, 'monitorable')
             ->isStatus(SiteMetric::STATUS_ACTIVE);
     }

     public function baselineMonitoringHistoric()
     {
         return $this->morphMany(SiteMetric::class, 'monitorable')
             ->isStatus(SiteMetric::STATUS_ARCHIVED);
     }

 }