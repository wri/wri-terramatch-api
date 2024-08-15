<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync roles and permissions with the configuration';

    public function handle()
    {
        Artisan::call('cache:clear');

        $permissionKeys = array_keys(config('wri.permissions.permissions'));
        foreach ($permissionKeys as $key) {
            if (! Permission::where('name', $key)->exists()) {
                Permission::create(['name' => $key]);
            }
        }

        $roles = config('wri.permissions.roles');
        foreach ($roles as $roleName => $permissions) {
            /** @var Role $role */
            $role = Role::where('name', $roleName)->first();
            if (empty($role)) {
                $role = Role::create(['name' => $roleName]);
            }
            $role->syncPermissions($permissions);
        }
    }
}
