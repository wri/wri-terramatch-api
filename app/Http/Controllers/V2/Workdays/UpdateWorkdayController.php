<?php

namespace App\Http\Controllers\V2\Workdays;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Workdays\UpdateWorkdayRequest;
use App\Http\Resources\V2\Workdays\WorkdayResource;
use App\Models\V2\Workdays\Workday;

class UpdateWorkdayController extends Controller
{
    public function __invoke(Workday $workday, UpdateWorkdayRequest $request): WorkdayResource
    {
        $this->authorize('read', $workday->workdayable);
        $workday->update($request->validated());
        $workday->save();

        return new WorkdayResource($workday);
    }
}
