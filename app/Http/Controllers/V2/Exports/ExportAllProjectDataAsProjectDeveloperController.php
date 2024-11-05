<?php

namespace App\Http\Controllers\V2\Exports;

use App\Http\Controllers\Controller;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\ExportAllProjectDataAsProjectDeveloperJob;
use App\Models\DelayedJob;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ExportAllProjectDataAsProjectDeveloperController extends Controller
{
    public function __invoke(Request $request, Project $project)
    {
        ini_set('memory_limit', '-1');
        $form = $this->getForm(Project::class, $project->framework_key);
        $this->authorize('export', [Project::class, $form, $project]);

        try {
            $binary_data = Redis::get('exports:project:'.$project->id);

            if (! $binary_data) {
                $delayedJob = DelayedJob::create();
                $job = new ExportAllProjectDataAsProjectDeveloperJob(
                    $delayedJob->id,
                    $form->uuid,
                    $project->id
                );
                dispatch($job);

                return (new DelayedJobResource($delayedJob))->additional(['message' => "Export for project $project->id is being processed"]);
            } else {
                $filename = storage_path('./'.Str::of($project->name)->replace(['/', '\\'], '-') . ' full export - ' . now() . '.zip');
                file_put_contents($filename, $binary_data);

                return response()->download($filename)->deleteFileAfterSend();
            }
        } catch (\Exception $e) {
            Log::error('Error during export for single project : ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred during single project export'], 500);
        }
    }

    private function getForm(string $modelClass, string $framework)
    {
        return Form::where('model', $modelClass)
            ->where('framework_key', $framework)
            ->firstOrFail();
    }
}
