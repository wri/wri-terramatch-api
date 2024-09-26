<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\General\ListingCollection;
use App\Models\V2\Organisation;
use Illuminate\Http\Request;

class OrganisationListingController extends Controller
{
    public function __invoke(Request $request): ListingCollection
    {
        $this->authorize('listing', Organisation::class);

        if (! empty($request->query('search'))) {
            $qry = Organisation::search($request->query('search'))
                ->where('status', Organisation::STATUS_APPROVED);
            $qry->limit(25);
        } else {
            $qry = Organisation::isStatus(Organisation::STATUS_APPROVED)
                ->limit(25);
        }

        $collection = $qry->orderBy('name')->get();

        return new ListingCollection($collection);
    }
}
