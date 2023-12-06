<?php

namespace App\Http\Controllers\V2\CoreTeamLeader;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreCoreTeamLeaderRequest;
use App\Http\Resources\V2\CoreTeamLeaderResource;
use App\Models\V2\CoreTeamLeader;
use App\Models\V2\Organisation;

class StoreCoreTeamLeaderController extends Controller
{
    public function __invoke(StoreCoreTeamLeaderRequest $storeCoreTeamLeaderRequest): CoreTeamLeaderResource
    {
        $model = Organisation::isUuid($storeCoreTeamLeaderRequest->organisation_id)->firstOrFail();
        $this->authorize('read', $model);

        $coreTeamLeader = CoreTeamLeader::create($storeCoreTeamLeaderRequest->all());

        return new CoreTeamLeaderResource($coreTeamLeader);
    }
}
