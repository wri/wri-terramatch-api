<?php

namespace App\Http\Controllers\V2\LeadershipTeam;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateLeadershipTeamRequest;
use App\Http\Resources\V2\LeadershipTeamResource;
use App\Models\V2\LeadershipTeam;

class UpdateLeadershipTeamController extends Controller
{
    public function __invoke(LeadershipTeam $leadershipTeam, UpdateLeadershipTeamRequest $updateLeadershipTeamRequest): LeadershipTeamResource
    {
        $this->authorize('read', $leadershipTeam->organisation);
        $leadershipTeam->update($updateLeadershipTeamRequest->validated());
        $leadershipTeam->save();

        return new LeadershipTeamResource($leadershipTeam);
    }
}
