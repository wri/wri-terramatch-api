<?php

namespace App\Http\Controllers\V2\Workdays;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Workdays\WorkdayResource;
use App\Models\V2\Workdays\Workday;

class DeleteWorkdayController extends Controller
{
    public function __invoke(Workday $workday): WorkdayResource
    {
        $this->authorize('update', $workday->workdayable);
        $workday->delete();
        $workday->save();

        return new WorkdayResource($workday);
    }
}
