<?php

namespace App\Http\Resources\V2\Files\Gallery;

use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\V2\User;
use App\Http\Resources\V2\User\UserLiteResource;


class GalleryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'file_url' => $this->getFullUrl(),
            'thumb_url' => $this->getFullUrl('thumbnail'),
            'name' => $this->name,
            'file_name' => $this->file_name,
            'created_date' => $this->created_at,
            'model_name' => $this->getModelName(),
            'is_public' => (bool) $this->is_public,
            'is_cover' => (bool) $this->is_cover,
            'location' => [
                'lat' => (float) $this->lat ?? null,
                'lng' => (float) $this->lng ?? null,
            ],
            'mime_type' => $this->mime_type,
            'file_size' => $this->size,
            'collection_name' => $this->collection_name,
            'photographer' => $this->photographer,
            'created_by' => new UserLiteResource(User::find($this->created_by)),
        ];
    }

    private function getModelName(): ?string
    {
        switch ($this->model_type) {
            case NurseryReport::class:
                $parentName = 'nursery-report';

                break;
            case SiteReport::class:
                $parentName = 'site-report';

                break;
            case ProjectReport::class:
                $parentName = 'project-report';

                break;
            case Site::class:
                $parentName = 'site';

                break;
            case Nursery::class:
                $parentName = 'nursery';

                break;
            case Project::class:
                $parentName = 'project';

                break;
            case ProjectMonitoring::class:
                $parentName = 'project-monitoring';

                break;
            case SiteMonitoring::class:
                $parentName = 'site-monitoring';

                break;
        }

        if (empty($parentName)) {
            throw new ModelNotFoundException();
        }

        return $parentName;
    }
}
