<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class EditHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'content' => $this->content,

            'organisation_name' => $this->organisation_name,
            'project_name' => $this->project_name,
            'framework_name' => $this->framework_name,
            'model' => $this->getModelResource($this->editable),
            'comments' => $this->comments,

            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }

    private function getModelResource($model): array
    {
        $modelKey = Str::snake(class_basename($model));

        return array_merge([
            'id' => $model->id,
            'type' => $modelKey,
            'name' => $model->name,
        ], $this->extraProps($modelKey, $model));
    }

    private function extraProps($modelKey, $model): array
    {
        switch ($modelKey) {
            case 'site':
                return [
                    'name_with_id' => data_get($model, 'name_with_id'),
                    'control_site' => data_get($model, 'control_site', 'false'),
                ];

            default:
                return [];
        }
    }
}
