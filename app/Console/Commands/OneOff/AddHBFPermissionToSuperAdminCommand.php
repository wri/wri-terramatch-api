<?php

namespace App\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AddHBFPermissionToSuperAdminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:add-hbf-permission-to-super-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add HBF permission to super admin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $superAdminRole = Role::where('name', 'admin-super')->first();
        $superAdminRole->givePermissionTo('framework-hbf');
    }
}
