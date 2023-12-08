<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundProgramme;
use App\Resources\Resource;

class TerrafundAimResource extends Resource
{
    public function __construct(TerrafundProgramme $programme)
    {
        $this->terrafund_programme_id = $programme->id;
        $this->trees_planted_goal = $programme->trees_planted;
        $this->trees_planted_count = $programme->trees_planted_count;
        $this->jobs_created_goal = $programme->jobs_created;
        $this->jobs_created_count = $programme->jobs_created_count;
    }
}
