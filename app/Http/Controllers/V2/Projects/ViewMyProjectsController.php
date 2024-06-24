<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Http\Resources\V2\Projects\ProjectsCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ViewMyProjectsController extends Controller
{
    public function __invoke(Request $request): ProjectsCollection
    {
        $user = Auth::user();

        return new ProjectsCollection($user->projects);
    }
}
