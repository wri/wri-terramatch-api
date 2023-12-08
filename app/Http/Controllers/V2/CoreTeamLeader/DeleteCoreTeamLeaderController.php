<?php

namespace App\Http\Controllers\V2\CoreTeamLeader;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\CoreTeamLeaderResource;
use App\Models\V2\CoreTeamLeader;

class DeleteCoreTeamLeaderController extends Controller
{
    public function __invoke(CoreTeamLeader $coreTeamLeader): CoreTeamLeaderResource
    {
        $this->authorize('update', $coreTeamLeader->organisation);
        $coreTeamLeader->delete();
        $coreTeamLeader->save();

        return new CoreTeamLeaderResource($coreTeamLeader);
    }
}
