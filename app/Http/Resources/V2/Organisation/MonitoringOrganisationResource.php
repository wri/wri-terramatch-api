<?php

namespace App\Http\Resources\V2\Organisation;

use App\Http\Resources\V2\TreeSpecies\TreeSpeciesResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitoringOrganisationResource extends JsonResource
{
    public function toArray($request)
    {
        $orRelation = DB::table('organisation_user')
            ->select('status')
            ->where('user_id', Auth::user()->id)
            ->where('organisation_id', $this->id)
            ->first();

        $data = [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'users_status' => data_get($orRelation, 'status', 'na'),
            'readable_status' => $this->readable_status,
            'type' => $this->type,
            'private' => $this->private,

            'name' => $this->name,
            'phone' => $this->phone,
            'hq_street_1' => $this->hq_street_1,
            'hq_street_2' => $this->hq_street_2,
            'hq_city' => $this->hq_city,
            'hq_state' => $this->hq_state,
            'hq_zipcode' => $this->hq_zipcode,
            'hq_country' => $this->hq_country,

            'countries' => $this->countries,
            'languages' => $this->languages,

            'founding_date' => $this->founding_date,
            'description' => $this->description,

            'tree_species' => TreeSpeciesResource::collection($this->treeSpeciesHistorical),

            'web_url' => $this->web_url,
            'facebook_url' => $this->facebook_url,
            'instagram_url' => $this->instagram_url,
            'linkedin_url' => $this->linkedin_url,
            'twitter_url' => $this->twitter_url,

            'fin_start_month' => $this->fin_start_month,
            'fin_budget_3year' => $this->fin_budget_3year,
            'fin_budget_2year' => $this->fin_budget_2year,
            'fin_budget_1year' => $this->fin_budget_1year,
            'fin_budget_current_year' => $this->fin_budget_current_year,

            'ha_restored_total' => $this->ha_restored_total,
            'ha_restored_3year' => $this->ha_restored_3year,
            'trees_grown_total' => $this->trees_grown_total,
            'trees_grown_3year' => $this->trees_grown_3year,
            'tree_care_approach' => $this->tree_care_approach,
            'relevant_experience_years' => $this->relevant_experience_years,

            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
            'tags' => $this->buildTagList(),
        ];

        return $this->appendFilesToResource($data);
    }

    private function buildTagList(): array
    {
        $list = [];
        foreach ($this->tags as $tag) {
            $list[$tag->slug] = $tag->name ;
        }

        return $list;
    }
}
