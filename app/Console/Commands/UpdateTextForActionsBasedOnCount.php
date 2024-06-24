<?php

namespace App\Console\Commands;

use App\Models\V2\Action;
use Illuminate\Console\Command;

class UpdateTextForActionsBasedOnCount extends Command
{
    protected $signature = 'update-text-for-actions-based-on-count';

    protected $description = 'Update text property in action, based on the count of nurseries and sites';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Action::where('created_at', '>=', '2023-12-01 00:00:00')->chunk(100, function ($actions) {
            foreach ($actions as $action) {
                $this->info('Updating action: ' . $action->id);
                $action->text = $this->getText($action);
                $this->info('text: ' . $action->text);
                $action->save();
            }
        });
    }

    private function getText($action)
    {
        $project = $action->project;
        $message = '';
        $nurseryCount = $project->nurseries()->count();
        $siteCount = $project->sites()->count();

        if ($nurseryCount != 0 && $siteCount != 0) {
            $message = 'Project, site and nursery reports available';
        } elseif ($nurseryCount > 0) {
            $message = 'Project and nursery reports available';
        } elseif ($siteCount > 0) {
            $message = 'Project and site reports available';
        } else {
            $message = 'Project report available';
        }

        return $message;
    }
}
