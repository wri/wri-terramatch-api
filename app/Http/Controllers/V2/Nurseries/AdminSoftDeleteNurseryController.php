<?php

namespace App\Http\Controllers\V2\Nurseries;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Nurseries\NurseryResource;
use App\Models\V2\Nurseries\Nursery;
use Illuminate\Http\Request;

class AdminSoftDeleteNurseryController extends Controller
{
    public function __invoke(Request $request, Nursery $nursery): NurseryResource
    {
        $this->authorize('delete', $nursery);

        $nursery->delete();

        return new NurseryResource($nursery);
    }
}
