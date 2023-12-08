<?php

namespace App\Resources;

use App\Models\ElevatorVideo as ElevatorVideoModel;

class ElevatorVideoResource
{
    public function __construct(ElevatorVideoModel $elevatorVideo)
    {
        $this->id = $elevatorVideo->id;
        $this->upload_id = $elevatorVideo->upload_id;
        $this->preview = ! is_null($elevatorVideo->upload_id) ? $elevatorVideo->upload->location : null;
        $this->status = $elevatorVideo->status;
        $this->uploaded_at = $elevatorVideo->created_at;
    }
}
