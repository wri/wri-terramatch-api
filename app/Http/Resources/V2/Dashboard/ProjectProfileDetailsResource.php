<?php

namespace App\Http\Resources\V2\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectProfileDetailsResource extends JsonResource
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
            'name' => $this->name,
            'descriptionObjetive' => $this->descriptionObjetive,
            'country' => $this->country,
            'countrySlug' => $this->countrySlug,
            'organisation' => $this->organisation,
            'survivalRate' => $this->survivalRate,
            'restorationStrategy' => $this->restorationStrategy,
            'targetLandUse' => $this->targetLandUse,
            'landTenure' => $this->landTenure,
        ];
    }
}
