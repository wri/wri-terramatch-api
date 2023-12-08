<?php

namespace App\Http\Controllers\V2\Seedings;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Seedings\SeedingResource;
use App\Models\V2\Seeding;

class DeleteSeedingController extends Controller
{
    public function __invoke(Seeding $seeding): SeedingResource
    {
        $this->authorize('update', $seeding->seedable);
        $seeding->delete();
        $seeding->save();

        return new SeedingResource($seeding);
    }
}
