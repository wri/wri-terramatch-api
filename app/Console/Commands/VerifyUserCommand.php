<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerifyUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'verify-user {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force the email verification of a user';

    public function handle(): int
    {
        $user = User::where('id', $this->argument('id'))->firstOrFail();
        $time = now();

        $user->email_address_verified_at = $time;
        $user->saveOrFail();

        $this->info("$user->first_name $user->last_name has been verified at $time");

        return 0;
    }
}
