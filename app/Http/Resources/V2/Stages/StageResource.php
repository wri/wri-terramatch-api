<?php

namespace App\Http\Resources\V2\Stages;

use App\Http\Resources\V2\Forms\FormResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class StageResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'deadline_at' => $this->deadline_at ? Carbon::parse($this->deadline_at, 'EST')->toISOString() : null,
            'funding_programme_id' => $this->funding_programme_id,
            'order' => $this->order,
            'form' => new FormResource($this->form),
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
