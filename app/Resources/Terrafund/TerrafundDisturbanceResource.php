<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundDisturbance as TerrafundDisturbanceModel;
use App\Resources\Resource;

class TerrafundDisturbanceResource extends Resource
{
    public function __construct(TerrafundDisturbanceModel $terrafundFile)
    {
        $this->id = $terrafundFile->id;
        $this->disturbanceable_type = $terrafundFile->disturbanceable_type;
        $this->disturbanceable_id = $terrafundFile->disturbanceable_id;
        $this->type = $terrafundFile->type;
        $this->description = $terrafundFile->description;
        $this->created_at = $terrafundFile->created_at;
        $this->updated_at = $terrafundFile->updated_at;
    }
}
