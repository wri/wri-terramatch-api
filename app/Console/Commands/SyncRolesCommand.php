<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// This command is only used by the roles table seeder for unit tests. The real state of the DB roles is managed
// in v3 now, and this command should no longer be run outside of the test suite.
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

    protected $PERMISSIONS_CONFIG = [
        'permissions' => [
            'framework-ppc' => 'Framework PPC',
            'framework-terrafund' => 'Framework Terrafund',
            'framework-enterprises' => 'Framework Terrafund Enterprises',
            'framework-terrafund-landscapes' => 'Framework Terrafund Landscapes',
            'framework-hbf' => 'Framework Harit Bharat Fund',
            'framework-epa-ghana-pilot' => 'Framework EPA Ghana Pilot',
            'framework-fundo-flora' => 'Framework Fundo Flora',
            'custom-forms-manage' => 'Manage custom forms',
            'users-manage' => 'Manage users',
            'monitoring-manage' => 'Manage monitoring',
            'reports-manage' => 'Manage Reports',
            'manage-own' => 'Manage own',
            'projects-read' => 'Read all projects',
            'polygons-manage' => 'Manage polygons',
            'media-manage' => 'Manage media',
            'view-dashboard' => 'View dashboard',
            'projects-manage' => 'Manage projects',
        ],
        'roles' => [
            'admin-super' => [
                'framework-terrafund',
                'framework-ppc',
                'framework-enterprises',
                'framework-terrafund-landscapes',
                'framework-hbf',
                'framework-epa-ghana-pilot',
                'framework-fundo-flora',
                'custom-forms-manage',
                'users-manage',
                'monitoring-manage',
                'reports-manage',
            ],
            'admin-ppc' => [
                'framework-ppc',
                'custom-forms-manage',
                'users-manage',
                'monitoring-manage',
                'reports-manage',
            ],
            'admin-terrafund' => [
                'framework-terrafund',
                'framework-enterprises',
                'framework-terrafund-landscapes',
                'custom-forms-manage',
                'users-manage',
                'monitoring-manage',
                'reports-manage' ,
            ],
            'admin-hbf' => [
                'framework-hbf',
                'custom-forms-manage',
                'users-manage',
                'monitoring-manage',
                'reports-manage',
            ],
            'admin-epa-ghana-pilot' => [
                'framework-epa-ghana-pilot',
                'custom-forms-manage',
                'users-manage',
                'monitoring-manage',
                'reports-manage',
            ],
            'admin-fundo-floral' => [
                'framework-fundo-flora',
                'custom-forms-manage',
                'users-manage',
                'monitoring-manage',
                'reports-manage',
            ],
            'project-developer' => [
                'manage-own',
            ],
            'project-manager' => [
                'projects-manage',
            ],
            'greenhouse-service-account' => [
                'projects-read',
                'polygons-manage',
                'media-manage',
            ],
            'research-service-account' => [
                'polygons-manage',
            ],
            'government' => [
                'view-dashboard',
            ],
            'funder' => [
                'view-dashboard',
            ],
        ],
    ];

    public function handle()
    {
        $permissionKeys = array_keys($this->PERMISSIONS_CONFIG['permissions']);
        foreach ($permissionKeys as $key) {
            if (! Permission::where('name', $key)->exists()) {
                Permission::create(['name' => $key]);
            }
        }

        $roles = $this->PERMISSIONS_CONFIG['roles'];
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
