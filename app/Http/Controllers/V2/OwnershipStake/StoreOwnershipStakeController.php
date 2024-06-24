<?php

namespace App\Http\Controllers\V2\OwnershipStake;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\StoreOwnershipStakeRequest;
use App\Http\Resources\V2\OwnershipStakeResource;
use App\Models\V2\Organisation;
use App\Models\V2\OwnershipStake;

class StoreOwnershipStakeController extends Controller
{
    public function __invoke(StoreOwnershipStakeRequest $storeOwnershipStakeRequest): OwnershipStakeResource
    {
        $model = Organisation::isUuid($storeOwnershipStakeRequest->organisation_id)->firstOrFail();
        $this->authorize('read', $model);

        $ownershipStake = OwnershipStake::create($storeOwnershipStakeRequest->all());

        return new OwnershipStakeResource($ownershipStake);
    }
}
