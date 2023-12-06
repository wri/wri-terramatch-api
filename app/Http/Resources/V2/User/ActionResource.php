<?php

namespace App\Http\Resources\V2\User;

use App\Http\Resources\V2\Nurseries\NurseryLiteResource;
use App\Http\Resources\V2\NurseryReports\NurseryReportLiteResource;
use App\Http\Resources\V2\ProjectReports\ProjectReportLiteResource;
use App\Http\Resources\V2\Projects\ProjectLiteResource;
use App\Http\Resources\V2\SiteReports\SiteReportLiteResource;
use App\Http\Resources\V2\Sites\SiteLiteResource;
use App\Http\Resources\V2\UpdateRequests\UpdateRequestLiteResource;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Http\Resources\Json\JsonResource;

class ActionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'targetable_type' => $this->getTargetType(),
            'targetable_id' => $this->targetable_id,
            'target' => $this->getTargetLiteResource(),
            'user_id' => $this->user_id,
            'type' => $this->type,
            'subtype' => $this->subtype,
            'title' => $this->title,
            'sub_title' => $this->sub_title,
            'text' => $this->text,
            'deleted_at' => $this->deleted_at,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }

    private function getTargetLiteResource()
    {
        switch ($this->targetable_type) {
            case Project::class:
                return ProjectLiteResource::make($this->targetable);
            case ProjectReport::class:
                return ProjectReportLiteResource::make($this->targetable)
                    ->withReportingTask();
            case Site::class:
                return SiteLiteResource::make($this->targetable);
            case SiteReport::class:
                return SiteReportLiteResource::make($this->targetable)
                    ->withReportingTask();
            case Nursery::class:
                return NurseryLiteResource::make($this->targetable);
            case NurseryReport::class:
                return NurseryReportLiteResource::make($this->targetable)
                    ->withReportingTask();
            case UpdateRequest::class:
                return UpdateRequestLiteResource::make($this->targetable);
            default:
                throw new \InvalidArgumentException('Unsupported target class');
        }
    }

    private function getTargetType()
    {
        switch ($this->targetable_type) {
            case Project::class:
                return 'Project';
            case ProjectReport::class:
                return 'ProjectReport';
            case Site::class:
                return 'Site';
            case SiteReport::class:
                return 'SiteReport';
            case Nursery::class:
                return 'Nursery';
            case NurseryReport::class:
                return 'NurseryReport';
            case UpdateRequest::class:
                return 'UpdateRequest';
            default:
                throw new \InvalidArgumentException('Unsupported target class');
        }
    }
}
