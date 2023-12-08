<?php

namespace App\Http\Controllers\V2\CoreTeamLeader;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateCoreTeamLeaderRequest;
use App\Http\Resources\V2\CoreTeamLeaderResource;
use App\Models\V2\CoreTeamLeader;

class UpdateCoreTeamLeaderController extends Controller
{
    public function __invoke(CoreTeamLeader $coreTeamLeader, UpdateCoreTeamLeaderRequest $updateCoreTeamLeaderRequest): CoreTeamLeaderResource
    {
        $this->authorize('read', $coreTeamLeader->organisation);
        $coreTeamLeader->update($updateCoreTeamLeaderRequest->validated());
        $coreTeamLeader->save();

        return new CoreTeamLeaderResource($coreTeamLeader);
    }
}
