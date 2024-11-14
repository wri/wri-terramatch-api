<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;

class BackfillTestProjectsOrgs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:backfill-test-projects-orgs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets the is_test flag on test projects and organisations';

    // In addition to these orgs, all projects within these orgs will get marked with is_test = true
    private const TEST_ORGS = [
        '7150d4ef-c785-49ed-9fb6-a28ef48ffb98', // 3SC PRODUCTION ORG (GT) - PLEASE IGNORE
        '15c2a8dc-d395-11ed-8014-0682e69bfbec', // TM Org
        '2470ced8-dc03-4d1e-9c64-b6445ac2c558', // test 0709
        'f936d363-8409-401e-8711-708909cfa205', // Edward's Testing Playground
        '15c07e71-d395-11ed-8014-0682e69bfbec', // Claire Trees Org
    ];

    // These test projects are in an org that is not itself a test org
    private const TEST_PROJECTS = [
        'be1d70ad-9f0e-486f-a85a-86c075d6a4d1', // Conservation International test project
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (Organisation::whereIn('uuid', self::TEST_ORGS)->get() as $organisation) {
            $organisation->update(['is_test' => true]);

            foreach ($organisation->projects as $project) {
                $project->update(['is_test' => true]);
            }
        }

        foreach (Project::whereIn('uuid', self::TEST_PROJECTS)->get() as $project) {
            $project->update(['is_test' => true]);
        }
    }
}
