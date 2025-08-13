<?php

namespace App\Models\Traits;

use App\Jobs\StatusUpdatedJob;

trait ReportsStatusChange
{
    public static function bootReportsStatusChange()
    {
        static::created(function ($model) {
            StatusUpdatedJob::dispatch($model);
        });

        static::updated(function ($model) {
            StatusUpdatedJob::dispatchIf($model->wasChanged('status'), $model);
        });
    }
}
