<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Projects\ProjectsCollection;
use App\Models\V2\Projects\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViewMyProjectsController extends Controller
{
    public function __invoke(Request $request): ProjectsCollection
    {
        $user = Auth::user();
        $userProjects = $user->projects->pluck('id')->toArray();

        $projects = Project::query()
            ->where('organisation_id', $user->organisation->id)
            ->orWhere(function (Builder $query) use ($userProjects) {
                $query->whereIn('id', $userProjects);
            })
            ->distinct()
            ->get();

        return new ProjectsCollection($projects);
    }
}
