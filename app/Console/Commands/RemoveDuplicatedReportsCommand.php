<?php

namespace App\Console\Commands;

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicatedReportsCommand extends Command
{
    protected $signature = 'process:project-reports {--due_at=} {--framework_key=} {--type=}';

    protected $description = 'Process reports by removing duplicates based on project_id and selecting earliest due_at';

    public function handle()
    {
        $specificDueAt = $this->option('due_at');
        $frameworkKey = $this->option('framework_key');
        $type = $this->option('type');

        if (! $specificDueAt || ! $frameworkKey || ! $type) {
            $this->error('The --due_at, --framework_key, and --type options are required.');

            return 1;
        }

        switch ($type) {
            case 'project':
                $reportModel = ProjectReport::class;

                break;
            case 'nursery':
                $reportModel = NurseryReport::class;

                break;
            case 'site':
                $reportModel = SiteReport::class;

                break;
            default:
                $this->error('Type must be one of "project", "nursery", or "site".');

                return 1;
        }

        $duplicateReports = DB::table("v2_{$type}_reports as pr1")
            ->select('pr1.id')
            ->join("v2_{$type}_reports as pr2", function ($join) use ($specificDueAt, $type) {
                $join->on("pr1.{$type}_id", '=', "pr2.{$type}_id")
                    ->on('pr1.due_at', '=', 'pr2.due_at')
                    ->whereRaw('pr1.id > pr2.id')
                    ->where('pr1.due_at', '=', $specificDueAt);
            })
            ->where('pr1.framework_key', $frameworkKey)
            ->get();

        $duplicateIds = $duplicateReports->pluck('id')->toArray();

        $reportsToDelete = $reportModel::whereIn('id', $duplicateIds)->get();

        $reportModel::whereIn('id', $duplicateIds)->delete();

        $this->info('Duplicate reports with framework_key "' . $frameworkKey . '" and due_at "' . $specificDueAt . '" removed successfully.');

        $this->showDeletedReports($reportsToDelete);

        $this->info('Reports processed successfully.');
    }

    protected function showDeletedReports($reportsToDelete)
    {
        $headers = ['ID', 'Due At', 'Framework Key'];
        $reportRows = [];

        foreach ($reportsToDelete as $report) {
            $reportRows[] = [$report->id, $report->due_at, $report->framework_key];
        }

        $this->info('Deleted reports:');
        $this->table($headers, $reportRows);
    }
}
