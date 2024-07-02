<?php

namespace App\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AddProjectDeveloperNewPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:add-project-developer-new-permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new permission to project-developer role.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $role = Role::where('name', 'project-developer')->first();
        $role->givePermissionTo(['view-dashboard']);
    }
}
