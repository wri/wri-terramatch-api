<?php

namespace App\Http\Controllers\V2\TreeSpecies;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\TreeSpecies\TreeSpeciesResource;
use App\Models\V2\TreeSpecies\TreeSpecies;

class DeleteTreeSpeciesController extends Controller
{
    public function __invoke(TreeSpecies $treeSpecies): TreeSpeciesResource
    {
        $this->authorize('update', $treeSpecies->speciesable);
        $treeSpecies->delete();
        $treeSpecies->save();

        return new TreeSpeciesResource($treeSpecies);
    }
}
