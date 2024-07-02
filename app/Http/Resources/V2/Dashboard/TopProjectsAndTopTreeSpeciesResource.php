<?php

namespace App\Http\Resources\V2\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class TopProjectsAndTopTreeSpeciesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'top_projects_most_planted_trees' => $this->top_projects_most_planted_trees,
            'top_tree_species_planted' => $this->top_tree_species_planted,
        ];
    }
}
