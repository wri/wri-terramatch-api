<?php

namespace App\Http\Controllers\V2\LeadershipTeam;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreLeadershipTeamRequest;
use App\Http\Resources\V2\LeadershipTeamResource;
use App\Models\V2\LeadershipTeam;
use App\Models\V2\Organisation;

class StoreLeadershipTeamController extends Controller
{
    public function __invoke(StoreLeadershipTeamRequest $storeLeadershipTeamRequest): LeadershipTeamResource
    {
        $model = Organisation::isUuid($storeLeadershipTeamRequest->organisation_id)->firstOrFail();
        $this->authorize('read', $model);

        $leadershipTeam = LeadershipTeam::create($storeLeadershipTeamRequest->all());

        return new LeadershipTeamResource($leadershipTeam);
    }
}
