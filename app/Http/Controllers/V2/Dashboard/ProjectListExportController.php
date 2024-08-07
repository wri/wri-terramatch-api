<?php

namespace App\Http\Controllers\V2\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use League\Csv\Writer;

class ProjectListExportController extends Controller
{
    public function __invoke(Request $request)
    {
        return $this->exportCsv($request);
    }

    public function exportCsv($request)
    {
        $activeProjectsController = new ActiveProjectsTableController();
        $perPage = $request->input('per_page', PHP_INT_MAX);
        $page = $request->input('page', 1);

        $projects = $activeProjectsController->getAllProjects($request, $perPage, $page);

        $headers = [
            'uuid' => 'UUID',
            'name' => 'Project Name',
            'organisation' => 'Organisation',
            'project_country' => 'Country',
            'number_of_trees_goal' => 'No. of Trees Goal',
            'trees_under_restoration' => 'No. of Trees Restored',
            'jobs_created' => 'No. of Jobs Created',
            'date_added' => 'Date Added',
            'number_of_sites' => 'No. of Sites',
            'number_of_nurseries' => 'No. of Nurseries',
        ];

        $filteredProjects = [];
        foreach ($projects as $project) {
            $filteredProject = [];
            foreach ($headers as $key => $label) {
                $filteredProject[$key] = $project[$key] ?? '';
            }
            $filteredProjects[] = $filteredProject;
        }

        $csv = Writer::createFromString('');

        $csv->insertOne(array_values($headers));

        foreach ($filteredProjects as $filteredProject) {
            $csv->insertOne(array_values($filteredProject));
        }

        $csvContent = $csv->toString();

        return response($csvContent, 200, [
          'Content-Type' => 'text/csv',
          'Content-Disposition' => 'attachment; filename=activeProject.csv',
      ]);

    }
}
