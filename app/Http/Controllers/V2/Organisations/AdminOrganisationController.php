<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Helpers\JsonResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Organisations\UpdateOrganisationRequest;
use App\Http\Resources\V2\Organisation\OrganisationResource;
use App\Http\Resources\V2\Organisation\OrganisationsCollection;
use App\Models\V2\Organisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class AdminOrganisationController extends Controller
{
    public function index(Request $request): OrganisationsCollection
    {
        $this->authorize('readAll', Organisation::class);
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $sortableColumns = [
            'created_at', '-created_at',
            'fin_budget_1year', '-fin_budget_1year',
            'name', '-name',
            'status', '-status',
            'type', '-type',
            'trees_grown_total', '-trees_grown_total',
        ];

        $qry = QueryBuilder::for(Organisation::class)
            ->allowedFilters([
                AllowedFilter::exact('status'),
                AllowedFilter::exact('type'),
                AllowedFilter::exact('hq_country'),
            ]);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        if ($request->query('search')) {
            $ids = Organisation::searchOrganisations(trim($request->query('search')))->pluck('id')->toArray();

            if (empty($ids)) {
                return new OrganisationsCollection([]);
            }
            $qry->whereIn('id', $ids);
        }

        $collection = $qry->paginate($perPage)
            ->appends(request()->query());

        return new OrganisationsCollection($collection);
    }

    public function show(Organisation $organisation, Request $request): OrganisationResource
    {
        $this->authorize('read', $organisation);

        return new OrganisationResource($organisation);
    }

    public function update(Organisation $organisation, UpdateOrganisationRequest $request): OrganisationResource
    {
        $this->authorize('update', $organisation);

        $organisation->update($request->all());

        if ($request->get('tags')) {
            $organisation->syncTags($request->get('tags'));
        }

        return new OrganisationResource($organisation);
    }

    public function destroy(Organisation $organisation, Request $request): JsonResponse
    {
        $this->authorize('delete', $organisation);

        $organisation->delete();

        return JsonResponseHelper::success(['Organisation has been deleted.'], 200);
    }
}
