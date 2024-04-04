<?php

namespace App\Console\Commands\OneOff;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateGreenhouseServiceAccountRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:create-greenhouse-service-account-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates the Greenhouse service account role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Keep this command idempotent
        $role = Role::where('name', 'greenhouse-service-account')->first();
        if ($role == null) {
            $role = Role::create(['name' => 'greenhouse-service-account']);
        }

        // Make sure all permissions in config/permissions have been created.
        $permissionKeys = array_keys(config('wri.permissions'));
        foreach ($permissionKeys as $key) {
            if (Permission::where('name', $key)->count() === 0) {
                Permission::create(['name' => $key]);
            }
        }

        $role->syncPermissions(['projects-read', 'polygons-manage', 'media-manage']);
    }
}
