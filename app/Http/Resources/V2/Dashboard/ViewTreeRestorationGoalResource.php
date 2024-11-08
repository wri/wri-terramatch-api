<?php

namespace App\Http\Resources\V2\Dashboard;

use Illuminate\Http\Resources\Json\JsonResource;

class ViewTreeRestorationGoalResource extends JsonResource
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
            'forProfitTreeCount' => (int) $this->resource['forProfitTreeCount'],
            'nonProfitTreeCount' => (int) $this->resource['nonProfitTreeCount'],
            'totalTreesGrownGoal' => (int) $this->resource['totalTreesGrownGoal'],
            'treesUnderRestorationActualTotal' => $this->resource['treesUnderRestorationActualTotal'],
            'treesUnderRestorationActualForProfit' => $this->resource['treesUnderRestorationActualForProfit'],
            'treesUnderRestorationActualNonProfit' => $this->resource['treesUnderRestorationActualNonProfit'],
        ];
    }
}
