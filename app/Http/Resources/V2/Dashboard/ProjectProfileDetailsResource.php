<?php

namespace App\Http\Resources\V2\Dashboard;

use App\Models\Traits\HasProjectCoverImage;
use App\Models\V2\Forms\FormOptionListOption;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectProfileDetailsResource extends JsonResource
{
    use HasProjectCoverImage;

    public function toArray($request)
    {
        $coverImage = $this->getProjectCoverImage($this->resource);

        $data = [
            'name' => $this->name,
            'descriptionObjetive' => $this->objectives,
            'country' => $this->getCountryLabel($this->country),
            'countrySlug' => $this->country,
            'organisation' => $this->organisation->type,
            'survivalRate' => $this->survival_rate,
            'restorationStrategy' => $this->restoration_strategy,
            'targetLandUse' => $this->land_use_types,
            'landTenure' => $this->land_tenure_project_area,
            'framework' => $this->framework_key,
            'cover_image' => $coverImage ? [
                'id' => $coverImage->id,
                'url' => $coverImage->getUrl(),
                'thumbnail' => $coverImage->getUrl('thumbnail'),
                'is_cover' => $coverImage->is_cover,
                'mime_type' => $coverImage->mime_type,
            ] : null,
        ];

        return $data;
    }

    public function getCountryLabel($slug)
    {
        return FormOptionListOption::where('slug', $slug)->value('label');
    }
}
