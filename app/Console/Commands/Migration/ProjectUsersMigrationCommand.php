<?php

namespace App\Console\Commands\Migration;

use App\Models\Programme;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\V2\Projects\Project;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProjectUsersMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:project-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate User / Project relations';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        DB::table('v2_project_users')->truncate();

        $collection = DB::table('programme_user')->get();
        foreach ($collection as $item) {
            $count++;
            $project = Project::where('old_id', $item->programme_id)
                ->where('old_model', Programme::class)
                ->first();

            if ($project) {
                DB::table('v2_project_users')->insert([
                    'project_id' => $project->id,
                    'user_id' => $item->user_id,
                    'status' => 'active',
                    'is_monitoring' => $item->is_monitoring,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
                $created++;
            }
        }

        $collection = DB::table('terrafund_programme_user')->get();
        foreach ($collection as $item) {
            $count++;
            $project = Project::where('old_id', $item->terrafund_programme_id)
                ->where('old_model', TerrafundProgramme::class)
                ->first();

            if ($project) {
                DB::table('v2_project_users')->insert([
                    'project_id' => $project->id,
                    'user_id' => $item->user_id,
                    'status' => 'active',
                    'is_monitoring' => false,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]);
                $created++;
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }
}
