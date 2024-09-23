<?php

namespace App\Console\Commands\Migration;

use App\Models\EditHistory;
use App\Models\Programme as PPCProgramme;
use App\Models\Site as PPCSite;
use App\Models\SiteSubmission as PPCSiteSubmission;
use App\Models\Submission as PPCProgrammeSubmission;
use App\Models\Terrafund\TerrafundNursery;
use App\Models\Terrafund\TerrafundNurserySubmission;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\Terrafund\TerrafundSite;
use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\UpdateRequests\UpdateRequest;
use Illuminate\Console\Command;

class UpdateRequestsMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:update-requests {--fresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate edit histories to update requests  V2 table';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            UpdateRequest::truncate();
        }

        $collection = EditHistory::all();
        foreach ($collection as $editHistory) {
            $count++;
            $map = $this->mapEditHistoryValues($editHistory);
            if (is_array($map)) {
                $newRecord = UpdateRequest::create($map);
                $created++;

                $newRecord->created_at = $editHistory->created_at;
                $newRecord->updated_at = $editHistory->updated_at;
                $newRecord->save();
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }

    private function mapEditHistoryValues(EditHistory $editHistory): ?array
    {
        $data = [
            'old_model' => EditHistory::class,
            'old_id' => $editHistory->id,

            'framework_key' => $editHistory->framework_id === 1 ? 'ppc' : 'terrafund',
            'organisation_id' => data_get($editHistory, 'organisation_id'),
            'project_id' => $this->getProjectId($editHistory->projectable_type, $editHistory->projectable_id),
            'created_by_id' => data_get($editHistory, 'created_by_user_id'),
            'status' => data_get($editHistory, 'status'),
            'content' => data_get($editHistory, 'content'),
            'feedback' => data_get($editHistory, 'comments'),
        ];



        $modelName = $this->getV2Model($editHistory->editable_type);
        $item = app($modelName)::where('old_model', $editHistory->editable_type)
            ->where('old_id', $editHistory->editable_id)
            ->first();

        if (! empty($item)) {
            $data['updaterequestable_id'] = $item->id;
            $data['updaterequestable_type'] = $modelName;

            return $data;
        }

        return null;
    }

    private function getV2Model(string $oldModel): ?string
    {
        switch($oldModel) {
            case TerrafundProgramme::class:
            case PPCProgramme::class:
                return Project::class;
            case TerrafundProgrammeSubmission::class:
            case PPCProgrammeSubmission::class:
                return ProjectReport::class;
            case TerrafundSite::class:
            case PPCSite::class:
                return Site::class;
            case TerrafundSiteSubmission::class:
            case PPCSiteSubmission::class:
                return SiteReport::class;
            case TerrafundNursery::class:
                return Nursery::class;
            case TerrafundNurserySubmission::class:
                return NurseryReport::class;
            default:
                return null;
        }
    }

    private function getProjectId(string $type, int $id): ?int
    {
        $project = Project::where('old_model', $type)
            ->where('old_id', $id)
            ->first();

        return empty($project) ? null : $project->id;
    }
}
