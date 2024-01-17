<?php

namespace App\Http\Controllers\V2\OwnershipStake;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\OwnershipStakeResource;
use App\Models\V2\OwnershipStake;

class DeleteOwnershipStakeController extends Controller
{
    public function __invoke(OwnershipStake $ownershipStake): OwnershipStakeResource
    {
        $this->authorize('update', $ownershipStake->organisation);
        $ownershipStake->delete();
        $ownershipStake->save();

        return new OwnershipStakeResource($ownershipStake);
    }
}
