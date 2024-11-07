<?php

namespace App\Http\Resources\V2\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class JobsCreatedResource extends JsonResource
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
            'totalJobsCreated' => (int) $this->totalJobsCreated,
            'total_ft' => (int) $this->total_ft,
            'total_pt' => (int) $this->total_pt,
            'total_men' => (int) $this->total_men,
            'total_pt_men' => (int) $this->total_pt_men,
            'total_ft_men' => (int) $this->total_ft_men,
            'total_women' => (int) $this->total_women,
            'total_pt_women' => (int) $this->total_pt_women,
            'total_ft_women' => (int) $this->total_ft_women,
            'total_youth' => (int) $this->total_youth,
            'total_pt_youth' => (int) $this->total_pt_youth,
            'total_ft_youth' => (int) $this->total_ft_youth,
            'total_non_youth' => (int) $this->total_non_youth,
            'total_pt_non_youth' => (int) $this->total_pt_non_youth,
            'total_ft_non_youth' => (int) $this->total_ft_non_youth,
        ];
    }
}
