<?php

namespace App\Http\Resources\V2\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V2\Forms\FormOptionListOption;

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
        $data = [
            'name' => $this->name,
            'descriptionObjetive' => $this->objectives,
            'country' => $this->getCountryLabel($this->country),
            'countrySlug' => $this->country,
            'organisation' => $this->organisation->type,
            'survivalRate' => $this->survival_rate,
            'restorationStrategy' => $this->restoration_strategy,
            'targetLandUse' => $this->land_use_types,
            'landTenure' => $this->land_tenure_project_area,
        ];

        return $this->appendFilesToResource($data);
    }

    public function getCountryLabel($slug)
    {
        return FormOptionListOption::where('slug', $slug)->value('label');
    }
}
