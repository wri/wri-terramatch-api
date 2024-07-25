<?php

namespace App\Http\Controllers\V2\Audits;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Audits\AuditCollection;
use App\Models\V2\EntityModel;
use Illuminate\Http\Request;

class AdminIndexAuditsController extends Controller
{
    public function __invoke(Request $request, EntityModel $entity)
    {
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);
        $this->authorize('readAll', $entity);
        $audits = $entity->audits()->orderByDesc('id')->paginate($perPage);

        return new AuditCollection($audits);
    }
}
