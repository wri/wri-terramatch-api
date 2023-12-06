<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class V2MigrateUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2-migrate-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update legacy users to V2';

    public function handle()
    {
        $users = User::whereNull('uuid')->get();
        foreach ($users as $user) {
            $user->uuid = Str::uuid()->toString();
            $user->save();
        }
    }
}
