<?php

namespace App\Http\Resources\V2\TreeSpecies;

use Illuminate\Http\Resources\Json\JsonResource;

class TreeSpeciesResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'amount' => $this->amount,
            'type' => $this->type,
            'collection' => $this->collection,
        ];
    }
}
