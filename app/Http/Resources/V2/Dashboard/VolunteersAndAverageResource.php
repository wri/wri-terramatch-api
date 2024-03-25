<?php

namespace App\Http\Resources\V2\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class VolunteersAndAverageResource extends JsonResource
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
            'total_volunteers' => $this->total_volunteers,
            'men_volunteers' => $this->men_volunteers,
            'women_volunteers' => $this->women_volunteers,
            'youth_volunteers' => $this->youth_volunteers,
            'non_youth_volunteers' => $this->non_youth_volunteers,
            'non_profit_survival_rate' => $this->non_profit_survival_rate,
            'enterprise_survival_rate' => $this->enterprise_survival_rate,
            'number_of_sites' => $this->number_of_sites,
            'number_of_nurseries' => $this->number_of_nurseries,
        ];
    }
}
