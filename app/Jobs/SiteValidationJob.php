<?php

namespace App\Jobs;

use App\Services\SiteValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class SiteValidationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    protected $uuid;

    protected $delayed_job_id;

    protected $siteUuid;

    public function __construct(string $siteUuid)
    {
        $this->siteUuid = $siteUuid;
    }

    public function handle(SiteValidationService $siteValidationService)
    {
        $value = $siteValidationService->validateSite($this->siteUuid);
        Redis::set('dashboard:sitevalidation|'.$this->siteUuid, json_encode($value));
    }
}
