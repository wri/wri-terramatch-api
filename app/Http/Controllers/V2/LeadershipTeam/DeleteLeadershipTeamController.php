<?php

namespace App\Http\Controllers\V2\LeadershipTeam;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\LeadershipTeamResource;
use App\Models\V2\LeadershipTeam;

class DeleteLeadershipTeamController extends Controller
{
    public function __invoke(LeadershipTeam $leadershipTeam): LeadershipTeamResource
    {
        $this->authorize('update', $leadershipTeam->organisation);
        $leadershipTeam->delete();
        $leadershipTeam->save();

        return new LeadershipTeamResource($leadershipTeam);
    }
}
