<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Organisations\StoreOrganisationRequest;
use App\Http\Requests\V2\Organisations\UpdateOrganisationRequest;
use App\Http\Resources\V2\Organisation\OrganisationResource;
use App\Http\Resources\V2\Organisation\OrganisationsCollection;
use App\Models\V2\Organisation;
use App\Models\V2\Shapefile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    public function index(Request $request): OrganisationsCollection
    {
        $this->authorize('read', Organisation::class);

        return new OrganisationsCollection($request->user()->all_my_organisations);
    }

    public function show(Organisation $organisation, Request $request): OrganisationResource
    {
        $this->authorize('read', $organisation);

        return new OrganisationResource($organisation);
    }

    public function store(StoreOrganisationRequest $request)
    {
        $this->authorize('create', Organisation::class);
        $user = $request->user();

        if (! empty($user->organisation_id)) {
            return new JsonResponse(['message' => 'Organisation already exists.'], 406);
        }

        $organisation = Organisation::create(array_merge($request->all(), ['status' => Organisation::STATUS_DRAFT]));

        if ($request->get('tags')) {
            $organisation->syncTags($request->get('tags'));
        }

        if ($request->get('shapefiles')) {
            foreach ($request->get('shapefiles') as $shapefile) {
                Shapefile::create([
                    'shapefileable_id' => $organisation->id,
                    'shapefileable_type' => Organisation::class,
                    'geojson' => $shapefile,
                ]);
            }
        }

        $user->organisation_id = $organisation->id;
        $user->save();

        return new OrganisationResource($organisation);
    }

    public function update(Organisation $organisation, UpdateOrganisationRequest $request): OrganisationResource
    {
        $this->authorize('update', $organisation);

        $organisation->update($request->all());

        if ($request->get('tags')) {
            $organisation->syncTags($request->get('tags'));
        }

        if ($request->get('shapefiles')) {
            Shapefile::query()
                ->where('shapefileable_id', $organisation->id)
                ->where('shapefileable_type', Organisation::class)
                ->delete();
            foreach ($request->get('shapefiles') as $shapefile) {
                Shapefile::create([
                    'shapefileable_id' => $organisation->id,
                    'shapefileable_type' => Organisation::class,
                    'geojson' => $shapefile,
                ]);
            }
        }

        return new OrganisationResource($organisation);
    }
}
