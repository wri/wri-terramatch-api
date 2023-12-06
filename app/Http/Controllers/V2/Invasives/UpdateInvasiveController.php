<?php

namespace App\Http\Controllers\V2\Invasives;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Invasives\UpdateInvasiveRequest;
use App\Http\Resources\V2\Invasives\InvasiveResource;
use App\Models\V2\Invasive;

class UpdateInvasiveController extends Controller
{
    public function __invoke(Invasive $invasive, UpdateInvasiveRequest $updateInvasiveRequest): InvasiveResource
    {
        $this->authorize('update', $invasive->invasiveable);
        $invasive->update($updateInvasiveRequest->validated());
        $invasive->save();

        return new InvasiveResource($invasive);
    }
}
