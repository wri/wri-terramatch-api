<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MoveUserRolesToV2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:move-user-roles-to-v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Moves role associations with V1 user to V2';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // chunk won't work here because of how we're modifying the table, and chunkById won't work because this table
        // doesn't have an id column, so we just have to load up all ~8k records at once
        $rows = DB::table('model_has_roles')
            ->where('model_type', '=', 'App\\Models\\User')
            ->get();
        foreach ($rows as $row) {
            $this->processRow($row);
        }

        // These two tables are a simple update
        DB::table('audits')
            ->where('user_type', '=', 'App\\Models\\User')
            ->update(['user_type' => User::class]);
        DB::table('state_histories')
            ->where('responsible_type', '=', 'App\\Models\\User')
            ->update(['responsible_type' => User::class]);
    }

    protected function processRow($row): void
    {
        $existsInV2 = DB::table('model_has_roles')
            ->where([
                'model_type' => User::class,
                'role_id' => $row->role_id,
                'model_id' => $row->model_id,
            ])
            ->exists();
        $query = DB::table('model_has_roles')
            ->where([
                'model_type' => $row->model_type,
                'role_id' => $row->role_id,
                'model_id' => $row->model_id,
            ]);
        if ($existsInV2) {
            $query->delete();
        } else {
            $query->update(['model_type' => User::class]);
        }
    }
}
