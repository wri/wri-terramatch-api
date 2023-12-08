<?php

namespace App\Http\Controllers\V2\TreeSpecies;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\TreeSpecies\UpdateTreeSpeciesRequest;
use App\Http\Resources\V2\TreeSpecies\TreeSpeciesResource;
use App\Models\V2\TreeSpecies\TreeSpecies;

class UpdateTreeSpeciesController extends Controller
{
    public function __invoke(TreeSpecies $treeSpecies, UpdateTreeSpeciesRequest $updateTreeSpeciesRequest): TreeSpeciesResource
    {
        $this->authorize('read', $treeSpecies->speciesable);
        $treeSpecies->update($updateTreeSpeciesRequest->validated());
        $treeSpecies->save();

        return new TreeSpeciesResource($treeSpecies);
    }
}
