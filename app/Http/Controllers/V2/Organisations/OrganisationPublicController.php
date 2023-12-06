<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\General\ListingCollection;
use App\Models\V2\Organisation;
use Illuminate\Http\Request;

class OrganisationPublicController extends Controller
{
    public function listing(Request $request): ListingCollection
    {
        $collection = Organisation::isStatus(Organisation::STATUS_APPROVED)
                        ->limit(25)
                        ->get();

        return new ListingCollection($collection);
    }
}
