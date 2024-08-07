<?php

namespace App\Console\Commands\Migration;

use App\Models\Programme;
use App\Models\Terrafund\TerrafundProgramme;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectInvite;
use App\Models\V2\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProjectInvitesMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:project-invites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Project User invites';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        DB::table('v2_project_invites')->truncate();

        $collection = DB::table('programme_invites')->get();
        foreach ($collection as $item) {
            $count++;
            $project = Project::where('old_id', $item->programme_id)
                ->where('old_model', Programme::class)
                ->first();

            if ($project) {
                $invite = ProjectInvite::create([
                    'project_id' => $project->id,
                    'email_address' => $item->email_address,
                    'token' => $item->token,
                    'accepted_at' => $item->accepted_at,
                ]);

                $invite->created_at = $item->created_at;
                $invite->updated_at = $item->updated_at;
                $invite->save();

                $created++;
            }
        }


        $collection = DB::table('terrafund_programme_invites')->get();
        foreach ($collection as $item) {
            $count++;
            $project = Project::where('old_id', $item->terrafund_programme_id)
                ->where('old_model', TerrafundProgramme::class)
                ->first();

            if ($project) {
                $user = User::where('email_address', $item->email_address)->first();
                $count = 0;
                if (! empty($user)) {
                    $count = DB::table('v2_project_users')->where('user_id', $user->id)
                        ->where('project_id', $project->id)
                        ->where('status', 'active')
                        ->count();
                }

                $invite = ProjectInvite::create([
                    'project_id' => $project->id,
                    'email_address' => $item->email_address,
                    'token' => $item->token,
                    'accepted_at' => $count > 0 ? $item->created_at : null,
                ]);

                $invite->created_at = $item->created_at;
                $invite->updated_at = $item->updated_at;
                $invite->save();

                $created++;
            }
        }

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }
}
