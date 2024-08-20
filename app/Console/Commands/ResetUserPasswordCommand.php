<?php

namespace App\Console\Commands;

use App\Models\V2\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetUserPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset-user-password {id} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the password of a given user';

    public function handle(): int
    {
        $user = User::where('id', $this->argument('id'))->firstOrFail();
        $password = $this->argument('password');

        $user->password = Hash::make($password);
        $user->saveOrFail();

        $this->info("Set $user->first_name $user->last_name's password to $password");

        return 0;
    }
}
