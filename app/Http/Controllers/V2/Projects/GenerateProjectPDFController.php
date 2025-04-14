<?php

namespace App\Http\Controllers\V2\Projects;

use App\Http\Controllers\Controller;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Response;

class GenerateProjectPDFController extends Controller
{
    public function __invoke(Project $project): Response
    {
        $this->authorize('view', $project);

        // TODO: Add PDF generation logic here

        return response()->noContent();
    }
}
