<?php

namespace App\Http\Controllers\V2\Seedings;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Seedings\UpdateSeedingRequest;
use App\Http\Resources\V2\Seedings\SeedingResource;
use App\Models\V2\Seeding;

class UpdateSeedingController extends Controller
{
    public function __invoke(Seeding $seeding, UpdateSeedingRequest $updateSeedingRequest): SeedingResource
    {
        $this->authorize('update', $seeding->seedable);
        $seeding->update($updateSeedingRequest->validated());
        $seeding->save();

        return new SeedingResource($seeding);
    }
}
