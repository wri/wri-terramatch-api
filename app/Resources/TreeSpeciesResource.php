<?php

namespace App\Resources;

use App\Models\TreeSpecies as ParentModel;
use App\Models\TreeSpeciesVersion as ChildModel;

class TreeSpeciesResource extends Resource
{
    public $id = null;
    public $pitch_id = null;
    public $name = null;
    public $is_native = null;
    public $count = null;
    public $price_to_plant = null;
    public $price_to_maintain = null;
    public $survival_rate = null;
    public $produces_food = null;
    public $produces_firewood = null;
    public $produces_timber = null;
    public $owner = null;
    public $season = null;

    public function __construct(ParentModel $parentModel, ?ChildModel $childModel)
    {
        $this->id = $parentModel->id;
        $this->pitch_id = $parentModel->pitch_id;
        $this->name = $childModel->name ?? null;
        $this->is_native = $childModel->is_native ?? null;
        $this->count = $childModel->count ?? null;
        $this->price_to_plant = $childModel->price_to_plant ?? null;
        $this->price_to_maintain = $childModel->price_to_maintain ?? null;
        $this->saplings = $childModel->saplings ?? null;
        $this->site_prep = $childModel->site_prep ?? null;
        $this->survival_rate = $childModel->survival_rate ?? null;
        $this->produces_food = $childModel->produces_food ?? null;
        $this->produces_firewood = $childModel->produces_firewood ?? null;
        $this->produces_timber = $childModel->produces_timber ?? null;
        $this->owner = $childModel->owner ?? null;
        $this->season = $childModel->season ?? null;
    }
}
