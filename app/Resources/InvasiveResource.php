<?php

namespace App\Resources;

use App\Models\Invasive as InvasiveModel;

class InvasiveResource extends Resource
{
    public function __construct(InvasiveModel $invasive)
    {
        $this->id = $invasive->id;
        $this->name = $invasive->name;
        $this->type = $invasive->type;
        $this->site_id = $invasive->site_id;
        $this->created_at = $invasive->created_at;
        $this->updated_at = $invasive->updated_at;
    }
}
