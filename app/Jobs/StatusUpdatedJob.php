<?php

namespace App\Jobs;

use App\Services\AnalyticsEventService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class StatusUpdatedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    protected $modelType;

    protected $uuid;

    protected $status;

    public function __construct($model)
    {
        $this->modelType = get_class($model);
        $this->uuid = $model->uuid;
        $this->status = $model->status;
    }

    public function handle(AnalyticsEventService $analyticsEventService)
    {
        $analyticsEventService->sendEvent(
            $this->uuid,
            'modelStatusUpdate',
            [
                'status' => $this->status,
                'modelType' => $this->modelType,
            ]
        );
    }
}
