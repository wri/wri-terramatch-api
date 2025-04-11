<?php

namespace App\Jobs;

use App\Clients\GreenhouseClient;
use App\Exceptions\ExternalAPIException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class NotifyGreenhouseJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected string $method;

    protected string $modelUuid;

    /**
     * @param string $method Must be a public method on GreenhouseClient (notifyPolygonUpdated or notifyMediaDeleted)
     * @param string $modelUuid The model UUID to include in the notification to Greenhouse
     */
    public function __construct(string $method, string $modelUuid)
    {
        $this->method = $method;
        $this->modelUuid = $modelUuid;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // try {
        //     App::make(GreenhouseClient::class)->{$this->method}($this->modelUuid);
        // } catch (ExternalAPIException $exception) {
        //     Log::error($exception->getMessage());
        // }
    }
}
