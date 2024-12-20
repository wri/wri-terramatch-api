<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DelayedJobProgressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
          'message' => $this->message ?? 'Job dispatched',
          'job_uuid' => $this->uuid,
          'proccessed_content' => $this->processed_content,
          'total_content' => $this->total_content,
          'progress_message' => $this->progress_message,
        ];
    }
}
