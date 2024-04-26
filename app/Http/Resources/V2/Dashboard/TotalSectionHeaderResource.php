<?php

namespace App\Http\Resources\V2\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class TotalSectionHeaderResource extends JsonResource
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
            'total_non_profit_count' => $this->total_non_profit_count,
            'total_enterprise_count' => $this->total_enterprise_count,
            'total_entries' => $this->total_entries,
            'total_hectares_restored' => $this->total_hectares_restored,
            'total_hectares_restored_goal' => $this->total_hectares_restored_goal,
            'total_trees_restored' => $this->total_trees_restored,
            'total_trees_restored_goal' => $this->total_trees_restored_goal,
            'country_name' => $this->country_name,
        ];
    }
}
