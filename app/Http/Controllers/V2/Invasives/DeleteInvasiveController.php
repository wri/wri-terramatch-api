<?php

namespace App\Http\Controllers\V2\Invasives;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Invasives\InvasiveResource;
use App\Models\V2\Invasive;

class DeleteInvasiveController extends Controller
{
    public function __invoke(Invasive $invasive): InvasiveResource
    {
        $this->authorize('update', $invasive->invasiveable);
        $invasive->delete();
        $invasive->save();

        return new InvasiveResource($invasive);
    }
}
