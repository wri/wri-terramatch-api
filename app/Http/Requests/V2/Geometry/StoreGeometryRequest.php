<?php

namespace App\Http\Requests\V2\Geometry;

use App\Models\V2\Sites\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StoreGeometryRequest extends FormRequest
{
    protected array $geometries;
    protected array $siteIds;
    protected array $sites;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'geometries' => 'required|array',
            'geometries.*.features' => 'required|array|min:1',
            'geometries.*.features.*.geometry.type' => 'required|string|in:Point,Polygon',
        ];
    }

    public function getGeometries(): array
    {
        if (! empty($this->geometries)) {
            return $this->geometries;
        }

        return $this->geometries = $this->input('geometries');
    }

    public function getSiteIds(): array
    {
        if (! empty($this->siteIds)) {
            return $this->siteIds;
        }

        return $this->siteIds = collect(
            data_get($this->getGeometries(), '*.features.*.properties.site_id')
        )->unique()->flatten()->toArray();
    }

    public function getSites()
    {
        if (! empty($this->sites)) {
            return $this->sites;
        }

        return $this->sites = Site::whereIn('uuid', $this->getSiteIds())->get();
    }

    /**
     * @throws ValidationException
     */
    public function validateGeometries(): void
    {
        // Make sure the data is coherent. Since we accept both Polygons and Points on this request, we have to
        // validate each geometry individually, rather than in the rules above
        foreach ($this->getGeometries() as $geometry) {
            $type = data_get($geometry, 'features.0.geometry.type');
            if ($type == 'Polygon') {
                // Require that we only have one geometry and that it has a site_id specified
                Validator::make($geometry, [
                    'features' => 'size:1',
                    'features.0.properties.site_id' => 'required|string',
                ])->validate();

            // This is guaranteed to be Point given the rules specified in rules()
            } else {
                // Require that all geometries in the collection are valid points, include estimated area, and that the
                // collection has exactly one unique site id.
                $siteIds = collect(data_get($geometry, 'features.*.properties.site_id'))->unique()->flatten()->toArray();
                Validator::make(['geometry' => $geometry, 'site_ids' => $siteIds], [
                    'geometry.features.*.geometry.type' => 'required|string|in:Point',
                    'geometry.features.*.geometry.coordinates' => 'required|array|size:2',
                    'geometry.features.*.properties.est_area' => 'required|numeric|min:1',
                    'site_ids' => 'required|array|size:1'
                ])->validate();
            }
        }

        // Structure this as a validation exception just to make the return shape of this endpoint consistent.
        Validator::make(['num_sites' => count($this->getSites()), 'num_site_ids' => count($this->getSiteIds())], [
            'num_sites' => 'same:num_site_ids'
        ])->validate();
    }
}
