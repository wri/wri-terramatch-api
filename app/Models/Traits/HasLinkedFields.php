<?php

namespace App\Models\Traits;

use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use Illuminate\Support\Arr;

trait HasLinkedFields
{
    public function getLinkedFieldInfo(array $params = null): ?array
    {
        $info = $this->getLinkedFieldConfig();

        if (is_array($info)) {
            $info['uuid'] = $this->getLinkedModelUuid($info, $params);
        }

        return $info;
    }

    protected function getLinkedFieldConfig(): ?array
    {
        $types = ['fields', 'file-collections', 'relations'];
        foreach (config('wri.linked-fields.models', []) as $modelKey => $cfgModel) {
            foreach ($types as $type) {
                if (Arr::has($cfgModel, $type. '.' . $this->linked_field_key)) {
                    $questionProps = data_get($cfgModel[$type], $this->linked_field_key);
                    $modelProps = [
                        'model-label' => data_get($cfgModel, 'label'),
                        'model-key' => $modelKey,
                        'model' => data_get($cfgModel, 'model'),
                        'link-type' => $type,
                    ];

                    return array_merge($modelProps, $questionProps);
                }
            }
        }

        return null;
    }

    public function getLinkedModelUuid($cfg, array $params = null): ?string
    {
        if (! empty(data_get($params, 'model_uuid'))) {
            return data_get($params, 'model_uuid');
        }

        return $this->getApplicationModelsUuids($cfg, $params);
    }

    public function getApplicationModelsUuids($cfg, array $params = null): ?string
    {
        $organisation = data_get($params, 'organisation', null);
        if (empty($organisation) && ! empty(data_get($params, 'organisation_uuid', null))) {
            $organisation = Organisation::isUuid(data_get($params, 'organisation_uuid'))->first();
        }

        $projectPitch = data_get($params, 'project-pitch', null);

        if (empty($projectPitch) && ! empty(data_get($params, 'project_pitch_uuid', null))) {
            $projectPitch = ProjectPitch::isUuid(data_get($params, 'project_pitch_uuid'))->first();
        }

        if ($organisation && empty($projectPitch)) {
            $projectPitch = $organisation->projectPitches()->first();
        }

        switch ($cfg['model-key']) {
            case 'organisation':
                return empty($organisation) ? null : $organisation->uuid;
            case 'project-pitch':
                return empty($projectPitch) ? null : $projectPitch->uuid;
            default:
                return null;
        }
    }
}
