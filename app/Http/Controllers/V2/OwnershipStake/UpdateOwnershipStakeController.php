<?php

namespace App\Http\Controllers\V2\OwnershipStake;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\UpdateOwnershipStakeRequest;
use App\Http\Resources\V2\OwnershipStakeResource;
use App\Models\V2\OwnershipStake;

class UpdateOwnershipStakeController extends Controller
{
    public function __invoke(OwnershipStake $ownershipStake, UpdateOwnershipStakeRequest $updateOwnershipStakeRequest): OwnershipStakeResource
    {
        $this->authorize('read', $ownershipStake->organisation);
        $ownershipStake->update($updateOwnershipStakeRequest->validated());
        $ownershipStake->save();

        return new OwnershipStakeResource($ownershipStake);
    }
}
