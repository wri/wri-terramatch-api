<?php

namespace App\Http\Controllers\V2\Organisations;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Tasks\TasksCollection;
use App\Models\V2\Organisation;
use App\Models\V2\Tasks\Task;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class ViewOrganisationTasksController extends Controller
{
    public function __invoke(Request $request, Organisation $organisation): TasksCollection
    {
        $this->authorize('read', $organisation);
        $perPage = $request->query('per_page') ?? config('app.pagination_default', 15);


        $sortableColumns = [
            'status', '-status',
            'period_key', '-period_key',
        ];

        $qry = QueryBuilder::for(Task::class)
            ->with(['project'])
            ->where('organisation_id', $organisation->id);

        if (in_array($request->query('sort'), $sortableColumns)) {
            $qry->allowedSorts($sortableColumns);
        }

        $collection = $qry->paginate($perPage);

        return new TasksCollection($collection);
    }
}
