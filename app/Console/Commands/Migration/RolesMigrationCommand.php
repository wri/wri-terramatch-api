<?php

namespace App\Console\Commands\Migration;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:roles {--fresh} {--log}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate roles & permissions';

    public function handle()
    {
        if ($this->option('log')) {
            echo('* * * Started * * * ' . $this->description . chr(10));
        }

        if ($this->option('fresh')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Role::truncate();
            Permission::truncate();
            DB::table('role_has_permissions')->truncate();
            DB::table('model_has_permissions')->truncate();
            DB::table('model_has_roles')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        Artisan::call('cache:clear');

        $permissionKeys = array_keys(config('wri.permissions'));
        foreach ($permissionKeys as $key) {
            if (Permission::where('name', $key)->count() === 0) {
                Permission::create(['name' => $key]);
            }
        }

        if (Role::where('name', 'admin-super')->count() === 0) {
            $role = Role::create(['name' => 'admin-super']);
            $role->givePermissionTo(['framework-terrafund', 'framework-ppc', 'custom-forms-manage', 'users-manage', 'monitoring-manage', 'reports-manage']);
        }

        if (Role::where('name', 'admin-ppc')->count() === 0) {
            $role = Role::create(['name' => 'admin-ppc']);
            $role->givePermissionTo(['framework-ppc', 'custom-forms-manage', 'users-manage', 'monitoring-manage', 'reports-manage']);
        }

        if (Role::where('name', 'admin-terrafund')->count() === 0) {
            $role = Role::create(['name' => 'admin-terrafund']);
            $role->givePermissionTo(['framework-terrafund', 'custom-forms-manage', 'users-manage', 'monitoring-manage', 'reports-manage']);
        }

        if (Role::where('name', 'project-developer')->count() === 0) {
            $role = Role::create(['name' => 'project-developer']);
            $role->givePermissionTo(['manage-own']);
        }

        User::whereIn('role', ['user','admin', 'terrafund-admin'])->get()
            ->each(function (User $user) {
                assignSpatieRole($user);
            });

        if ($this->option('log')) {
            echo('- - - Finished - - - ' . chr(10));
        }
    }
}
